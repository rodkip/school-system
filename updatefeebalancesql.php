<?php
// student_fee_query.php
function getStudentFeeQuery() {
    return "SELECT 
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
    
    -- Term fees with treatment logic
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
    
    -- Other fees
    fs.othersfee,
    
 -- Transport (fees and waivers without treatment rate multiplications)
COALESCE(te.firsttermtransport, 0) AS firsttermtransport,
COALESCE(te.secondtermtransport, 0) AS secondtermtransport,
COALESCE(te.thirdtermtransport, 0) AS thirdtermtransport,

COALESCE(te.firsttermtransportwaiver, 0) AS firsttermtransportwaiver,
COALESCE(te.secondtermtransportwaiver, 0) AS secondtermtransportwaiver,
COALESCE(te.thirdtermtransportwaiver, 0) AS thirdtermtransportwaiver,

    -- Payments and waivers
    COALESCE(SUM(fp.Cash), 0) AS totpayperyear,
    COALESCE(ce.firsttermfeewaiver, 0) AS firsttermfeewaiver,
    COALESCE(ce.secondtermfeewaiver, 0) AS secondtermfeewaiver,
    COALESCE(ce.thirdtermfeewaiver, 0) AS thirdtermfeewaiver,

    -- Total calculation (fees + transport - waivers)
    ROUND((
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
        ) +
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
        ) +
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
        )
        + (COALESCE(te.firsttermtransport, 0) * ce.feetreatmentrate * ce.childtreatmentrate)
        + (COALESCE(te.secondtermtransport, 0) * ce.feetreatmentrate * ce.childtreatmentrate)
        + (COALESCE(te.thirdtermtransport, 0) * ce.feetreatmentrate * ce.childtreatmentrate)
        - (COALESCE(ce.firsttermfeewaiver, 0) + COALESCE(ce.secondtermfeewaiver, 0) + COALESCE(ce.thirdtermfeewaiver, 0))
        - (
            COALESCE(te.firsttermtransportwaiver, 0) * ce.feetreatmentrate * ce.childtreatmentrate +
            COALESCE(te.secondtermtransportwaiver, 0) * ce.feetreatmentrate * ce.childtreatmentrate +
            COALESCE(te.thirdtermtransportwaiver, 0) * ce.feetreatmentrate * ce.childtreatmentrate
        )
    )) AS totcalfee,

    COUNT(fp.Cash) AS instalments

FROM feestructure fs
INNER JOIN (
    classdetails cd
    INNER JOIN (
        (feepayments fp
        INNER JOIN studentdetails sd ON fp.studentadmno = sd.studentadmno) 
        INNER JOIN classentries ce ON sd.studentadmno = ce.studentAdmNo
    ) ON (cd.academicyear = fp.academicyear) AND (cd.gradefullName = ce.gradefullname)
) ON fs.feeStructureName = ce.feegradename

LEFT JOIN transportentries te ON te.classentryfullname = ce.classentryfullname

WHERE sd.studentadmno = :searchadmno

GROUP BY 
    sd.studentadmno, sd.studentname, ce.gradefullname, 
    fs.EntryTerm, fs.boarding, ce.childTreatment, ce.feeTreatment, 
    fs.othersfee, ce.feetreatmentrate, ce.childtreatmentrate,
    ce.firsttermfeewaiver, ce.secondtermfeewaiver, ce.thirdtermfeewaiver,
    te.firsttermtransport, te.secondtermtransport, te.thirdtermtransport,
    te.firsttermtransportwaiver, te.secondtermtransportwaiver, te.thirdtermtransportwaiver,
    fs.feeStructureName

ORDER BY ce.gradefullname ASC";
}
?>