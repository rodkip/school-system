<?php
class FeeBalanceUpdater {
    private $dbh;
    
    public function __construct($dbConnection) {
        $this->dbh = $dbConnection;
    }
    
    public function updateStudentBalances($studentAdmNos) {
        if (empty($studentAdmNos)) {
            return false;
        }

        foreach ($studentAdmNos as $admNo) {
            $this->processStudent($admNo);
        }
        return true;
    }

    private function processStudent($searchadmno) {
        try {
            $yearlybal = 0;
            $arr = 0;
            
            $balanceSql = "SELECT 
                        sd.studentadmno, 
                        sd.studentname,  
                        ce.gradefullname,
                        ce.childstatus, 
                        fs.EntryTerm, 
                        fs.boarding, 
                        ce.childTreatment, 
                        ce.feeTreatment, 
                        ce.feetreatmentrate, 
                        ce.childtreatmentrate,
                        
                        -- Calculate term fees with selective treatment application
                        (
                            SELECT COALESCE(SUM(
                                CASE 
                                    WHEN vh.isfeetreatmentcalculations = 'Yes' 
                                    THEN fsv.firstterm * ce.feetreatmentrate * ce.childtreatmentrate
                                    ELSE fsv.firstterm
                                END
                            ), 0)
                            FROM feestructurevoteheadcharges fsv
                            JOIN voteheads vh ON fsv.votehead_id = vh.id
                            WHERE fsv.feestructurename = fs.feeStructureName
                        ) AS firsttermfee,
                        
                        (
                            SELECT COALESCE(SUM(
                                CASE 
                                    WHEN vh.isfeetreatmentcalculations = 'Yes' 
                                    THEN fsv.secondterm * ce.feetreatmentrate * ce.childtreatmentrate
                                    ELSE fsv.secondterm
                                END
                            ), 0)
                            FROM feestructurevoteheadcharges fsv
                            JOIN voteheads vh ON fsv.votehead_id = vh.id
                            WHERE fsv.feestructurename = fs.feeStructureName
                        ) AS secondtermfee,
                        
                        (
                            SELECT COALESCE(SUM(
                                CASE 
                                    WHEN vh.isfeetreatmentcalculations = 'Yes' 
                                    THEN fsv.thirdterm * ce.feetreatmentrate * ce.childtreatmentrate
                                    ELSE fsv.thirdterm
                                END
                            ), 0)
                            FROM feestructurevoteheadcharges fsv
                            JOIN voteheads vh ON fsv.votehead_id = vh.id
                            WHERE fsv.feestructurename = fs.feeStructureName
                        ) AS thirdtermfee,
                        
                        -- Others fee
                        fs.othersfee,
                        
                        -- Transport fees WITH treatment rates applied
                        COALESCE(te.firsttermtransport, 0) * ce.feetreatmentrate * ce.childtreatmentrate AS firsttermtransport,
                        COALESCE(te.secondtermtransport, 0) * ce.feetreatmentrate * ce.childtreatmentrate AS secondtermtransport,
                        COALESCE(te.thirdtermtransport, 0) * ce.feetreatmentrate * ce.childtreatmentrate AS thirdtermtransport,
                        
                        COALESCE(SUM(fp.Cash), 0) AS totpayperyear,
                        COALESCE(ce.firsttermfeewaiver, 0) AS firsttermfeewaiver,
                        COALESCE(ce.secondtermfeewaiver, 0) AS secondtermfeewaiver,
                        COALESCE(ce.thirdtermfeewaiver, 0) AS thirdtermfeewaiver,
                        
                        -- Calculate totals
                        ROUND((
                            firsttermfee + secondtermfee + thirdtermfee + fs.othersfee
                            + (COALESCE(te.firsttermtransport, 0) * ce.feetreatmentrate * ce.childtreatmentrate)
                            + (COALESCE(te.secondtermtransport, 0) * ce.feetreatmentrate * ce.childtreatmentrate)
                            + (COALESCE(te.thirdtermtransport, 0) * ce.feetreatmentrate * ce.childtreatmentrate)
                            - (COALESCE(ce.firsttermfeewaiver, 0) + COALESCE(ce.secondtermfeewaiver, 0) + COALESCE(ce.thirdtermfeewaiver, 0))
                        )) AS totcalfee,

                        COUNT(fp.Cash) AS instalments

                    FROM feestructure fs
                    INNER JOIN (classdetails cd
                        INNER JOIN ((feepayments fp
                        INNER JOIN studentdetails sd ON fp.studentadmno = sd.studentadmno) 
                        INNER JOIN classentries ce ON sd.studentadmno = ce.studentAdmNo) 
                        ON (cd.academicyear = fp.academicyear) 
                        AND (cd.gradefullName = ce.gradefullname)) 
                    ON fs.feeStructureName = ce.feegradename
                    LEFT JOIN transportentries te ON te.classentryfullname = ce.classentryfullname
                    GROUP BY 
                        sd.studentadmno, sd.studentname, ce.gradefullname, 
                        fs.EntryTerm, fs.boarding, ce.childTreatment, ce.feeTreatment, 
                        fs.firsttermfee, fs.secondtermfee, fs.thirdtermfee, fs.othersfee, 
                        ce.feetreatmentrate, ce.childtreatmentrate,
                        ce.firsttermfeewaiver, ce.secondtermfeewaiver, ce.thirdtermfeewaiver,
                        te.firsttermtransport, te.secondtermtransport, te.thirdtermtransport
                    HAVING sd.studentadmno = :searchadmno
                    ORDER BY ce.gradefullname ASC";

            $balanceQuery = $this->dbh->prepare($balanceSql);
            $balanceQuery->bindParam(':searchadmno', $searchadmno, PDO::PARAM_STR);
            $balanceQuery->execute();
            $results = $balanceQuery->fetchAll(PDO::FETCH_OBJ);

            if ($balanceQuery->rowCount() > 0) {
                foreach ($results as $row) {
                    $adjusted = $yearlybal + $arr;
                    $first_total = $row->firsttermbal + $adjusted;
                    $first_limit = $row->firsttermfeecal + $row->othersfeecal;
                    $second_total = $row->secondtermbal + $adjusted;
                    $second_limit = $row->secondtermfeecal;
                    $third_total = $row->thirdtermbal + $adjusted;
                    $third_limit = $row->thirdtermfeecal;
                    
                    $firsttermbalcal = max(0, min($first_total, $first_limit));
                    $secondtermbalcal = max(0, min($second_total, $second_limit));
                    $thirdtermbalcal = max(0, min($third_total, $third_limit));
                    
                    $arrears = $yearlybal + $arr;
                    $balperyear = $row->balperyear + $arrears;
                    $yearlybal += $row->balperyear + $arr;

                    $feebalancecode = $row->gradefullname . $row->studentadmno;

                    if ($this->feeBalanceExists($feebalancecode)) {
                        $this->updateFeeBalance($feebalancecode, $row, $arrears, $firsttermbalcal, $secondtermbalcal, $thirdtermbalcal, $balperyear);
                    }
                }
            }
        } catch (PDOException $e) {
            error_log("Balance update error for student $searchadmno: " . $e->getMessage());
        }
    }

    private function feeBalanceExists($feebalancecode) {
        $checkSql = "SELECT feebalancecode FROM feebalances WHERE feebalancecode = :feebalancecode";
        $checkQuery = $this->dbh->prepare($checkSql);
        $checkQuery->bindParam(':feebalancecode', $feebalancecode, PDO::PARAM_STR);
        $checkQuery->execute();
        return $checkQuery->rowCount() > 0;
    }

    private function updateFeeBalance($feebalancecode, $row, $arrears, $firsttermbalcal, $secondtermbalcal, $thirdtermbalcal, $balperyear) {
        $updateSql = "UPDATE feebalances SET 
                        childstatus = :childstatus,
                        arrears = :arrears,
                        firsttermbal = :firsttermbalcal,
                        secondtermbal = :secondtermbalcal,
                        thirdtermbal = :thirdtermbalcal,
                        yearlybal = :balperyear,
                        feetreatment = :feetreatment,
                        childtreatment = :childtreatment,
                        studentname = :studentname,
                        gradefullname = :gradefullname,
                        totalfee = :totcalfee,
                        totalpaid = :totpayperyear,
                        firsttermfee = :firsttermfeecal,
                        secondtermfee = :secondtermfeecal,
                        thirdtermfee = :thirdtermfeecal,
                        othersfee = :othersfeecal,
                        boarding = :boarding,
                        last_updated = NOW()
                    WHERE feebalancecode = :feebalancecode";

        $updateQuery = $this->dbh->prepare($updateSql);
        $updateQuery->execute([
            ':feebalancecode' => $feebalancecode,
            ':childstatus' => $row->childstatus,
            ':arrears' => $arrears,
            ':firsttermbalcal' => $firsttermbalcal,
            ':secondtermbalcal' => $secondtermbalcal,
            ':thirdtermbalcal' => $thirdtermbalcal,
            ':balperyear' => $balperyear,
            ':feetreatment' => $row->feeTreatment,
            ':childtreatment' => $row->childTreatment,
            ':studentname' => $row->studentname,
            ':gradefullname' => $row->gradefullname,
            ':totcalfee' => $row->totcalfee,
            ':totpayperyear' => $row->totpayperyear,
            ':firsttermfeecal' => $row->firsttermfeecal,
            ':secondtermfeecal' => $row->secondtermfeecal,
            ':thirdtermfeecal' => $row->thirdtermfeecal,
            ':othersfeecal' => $row->othersfeecal,
            ':boarding' => $row->boarding
        ]);
    }
}