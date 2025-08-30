<div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="viewbirthdays" class="modal fade">
    <div class="modal-dialog modal-lg">
        <div class="modal-content animate__animated animate__bounceIn">
            <!-- Modal Header -->
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                <h4 class="modal-title" style="color: white; font-weight: 600;">🎉 Birthday Calendar</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: #fff; opacity: 1; text-shadow: none;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-4" style="background-color: #f8f9fa;">
                <div class="row">
                    <div class="card-body">
                        <!-- Birthday Dashboard Section -->
                        <?php
                        // Get date information
                        $today_month_day = date('m-d');
                        $current_month = date('m');
                        $current_year = date('Y');
                        $today = new DateTime();
                        
                        // Fetch student birthdays for today with full date, ordered by date
                        $studentBdayQuery = "SELECT sd.studentname, sd.dateofbirth, ce.gradefullname
                        FROM studentdetails sd
                        JOIN classentries ce 
                        ON sd.studentadmno = ce.studentadmno
                        WHERE DATE_FORMAT(sd.dateofbirth, '%m-%d') = :today
                        AND ce.gradefullname = (
                            SELECT MAX(gradefullname)
                            FROM classentries
                            WHERE studentadmno = sd.studentadmno
                        )
                        ORDER BY DATE_FORMAT(sd.dateofbirth, '%m-%d') ASC";
   
                        $studentBdayStmt = $dbh->prepare($studentBdayQuery);
                        $studentBdayStmt->bindParam(':today', $today_month_day, PDO::PARAM_STR);
                        $studentBdayStmt->execute();
                        $studentBirthdays = $studentBdayStmt->fetchAll(PDO::FETCH_OBJ);
                        
                        // Fetch birthdays this week with full date, ordered by date
                        $startOfWeek = date('Y-m-d', strtotime('monday this week'));
                        $endOfWeek = date('Y-m-d', strtotime('sunday this week'));
                        $studentWeekBdayQuery = "SELECT sd.studentname, sd.dateofbirth, ce.gradefullname
                        FROM studentdetails sd
                        JOIN classentries ce 
                        ON sd.studentadmno = ce.studentadmno
                        WHERE DATE_FORMAT(sd.dateofbirth, '%m-%d') 
                            BETWEEN DATE_FORMAT(:startOfWeek, '%m-%d') 
                                AND DATE_FORMAT(:endOfWeek, '%m-%d')
                        AND DATE_FORMAT(sd.dateofbirth, '%m-%d') != :today
                        AND ce.gradefullname = (
                            SELECT MAX(gradefullname)
                            FROM classentries
                            WHERE studentadmno = sd.studentadmno
                        )
                        ORDER BY DATE_FORMAT(sd.dateofbirth, '%m-%d') ASC";

                        $studentWeekBdayStmt = $dbh->prepare($studentWeekBdayQuery);
                        $studentWeekBdayStmt->bindParam(':startOfWeek', $startOfWeek, PDO::PARAM_STR);
                        $studentWeekBdayStmt->bindParam(':endOfWeek', $endOfWeek, PDO::PARAM_STR);
                        $studentWeekBdayStmt->bindParam(':today', $today_month_day, PDO::PARAM_STR);
                        $studentWeekBdayStmt->execute();
                        $studentWeekBirthdays = $studentWeekBdayStmt->fetchAll(PDO::FETCH_OBJ);
                        
                        // Fetch birthdays this month with full date, ordered by date (excluding today and this week)
                        $studentMonthBdayQuery = "SELECT sd.studentname, sd.dateofbirth, ce.gradefullname
                        FROM studentdetails sd
                        JOIN classentries ce ON sd.studentadmno = ce.studentadmno
                        WHERE MONTH(sd.dateofbirth) = :month
                        AND DATE_FORMAT(sd.dateofbirth, '%m-%d') NOT BETWEEN DATE_FORMAT(:startOfWeek, '%m-%d') 
                                                                        AND DATE_FORMAT(:endOfWeek, '%m-%d')
                        AND DATE_FORMAT(sd.dateofbirth, '%m-%d') != :today
                        AND ce.gradefullname = (
                            SELECT MAX(gradefullname)
                            FROM classentries
                            WHERE studentadmno = sd.studentadmno
                        )
                        ORDER BY DATE_FORMAT(sd.dateofbirth, '%m-%d') ASC";

                        $studentMonthBdayStmt = $dbh->prepare($studentMonthBdayQuery);
                        $studentMonthBdayStmt->bindParam(':month', $current_month, PDO::PARAM_STR);
                        $studentMonthBdayStmt->bindParam(':startOfWeek', $startOfWeek, PDO::PARAM_STR);
                        $studentMonthBdayStmt->bindParam(':endOfWeek', $endOfWeek, PDO::PARAM_STR);
                        $studentMonthBdayStmt->bindParam(':today', $today_month_day, PDO::PARAM_STR);
                        $studentMonthBdayStmt->execute();
                        $studentMonthBirthdays = $studentMonthBdayStmt->fetchAll(PDO::FETCH_OBJ);
                        ?>

                        <div class="text-center mb-4">
                            <h1 style="color: #764ba2; font-weight: 700; text-shadow: 1px 1px 3px rgba(0,0,0,0.1);">🎂 Birthdays 🎂</h1>
                            <p class="text-muted"><?php echo date('l, F j, Y'); ?></p>
                            <div class="confetti"></div>
                        </div>
                        
                        <!-- Today's Birthdays -->
                        <div class="birthday-section animate__animated animate__fadeIn">
                            <div class="section-header" style="background: linear-gradient(135deg, #ff9a9e 0%, #fad0c4 100%); color: white; padding: 12px; border-radius: 8px; margin-bottom: 15px; box-shadow: 0 3px 10px rgba(0,0,0,0.1);">
                                <h4 style="margin: 0; font-weight: 600;">🎉 Today's Birthdays (<span style="font-weight: bold;"><?php echo date('F j'); ?></span>)</h4>
                            </div>
                            <div class="birthday-list">
                                <?php if (count($studentBirthdays) > 0): ?>
                                    <?php foreach ($studentBirthdays as $person): 
                                        $birthDate = new DateTime($person->dateofbirth);
                                        $age = $current_year - $birthDate->format('Y');
                                    ?>
                                        <div class="birthday-item animate__animated animate__fadeInLeft" style="background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%); padding: 15px; margin-bottom: 12px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); display: flex; align-items: center; border-left: 5px solid #ff9a9e;">
                                            <div style="font-size: 28px; margin-right: 15px; color: #ff6b6b;">🎂</div>
                                            <div style="flex-grow: 1;">
                                                <strong style="font-size: 1.1em; color: #495057;"><?php echo htmlentities($person->studentname); ?></strong>
                                                <div style="color: #6c757d; font-size: 0.9em;">
                                                    Born on <?php echo $birthDate->format('F j, Y'); ?> (turning <?php echo $age; ?> today!)<br>
                                                    Latest Grade: <?php echo htmlspecialchars($person->gradefullname); ?>
                                                </div>

                                            </div>
                                            <div style="font-size: 28px; color: #ff8787;">🥳</div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="no-birthdays animate__animated animate__fadeIn" style="background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%); padding: 15px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); text-align: center; border-left: 5px solid #adb5bd;">
                                        <span style="font-size: 1.1em; color: #6c757d;">🎈 No birthdays today 🎈</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- This Week's Birthdays -->
                        <div class="birthday-section animate__animated animate__fadeIn" style="margin-top: 30px;">
                            <div class="section-header" style="background: linear-gradient(135deg, #74c0fc 0%, #4dabf7 100%); color: white; padding: 12px; border-radius: 8px; margin-bottom: 15px; box-shadow: 0 3px 10px rgba(0,0,0,0.1);">
                                <h4 style="margin: 0; font-weight: 600;">📅 Upcoming Birthdays This Week</h4>
                                <div style="font-size: 0.9em; opacity: 0.9;"><?php echo date('M j', strtotime('tomorrow')); ?> - <?php echo date('M j', strtotime('sunday this week')); ?></div>
                            </div>
                            <div class="birthday-list">
                                <?php if (count($studentWeekBirthdays) > 0): ?>
                                    <?php foreach ($studentWeekBirthdays as $person): 
                                        $birthDate = new DateTime($person->dateofbirth);
                                        $age = $current_year - $birthDate->format('Y');
                                        $upcomingDate = new DateTime($current_year . '-' . $birthDate->format('m-d'));
                                        $diff = $upcomingDate->diff($today);
                                        $daysDifference = $diff->days;
                                        $isPast = $upcomingDate < $today;
                                    ?>
                                        <div class="birthday-item animate__animated animate__fadeInRight" style="background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%); padding: 15px; margin-bottom: 12px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); display: flex; align-items: center; border-left: 5px solid #74c0fc;">
                                            <div style="font-size: 28px; margin-right: 15px; color: #4dabf7;">🗓️</div>
                                            <div style="flex-grow: 1;">
                                                <strong style="font-size: 1.1em; color: #495057;"><?php echo htmlentities($person->studentname); ?></strong>
                                                <div style="color: #6c757d; font-size: 0.9em;">
                                                    <?php echo $birthDate->format('F j'); ?> 
                                                    <?php if($isPast): ?>
                                                        (<span style="color: #f06595;"><?php echo $daysDifference; ?> day<?php echo $daysDifference > 1 ? 's' : ''; ?> ago</span> • <?php echo $age; ?> years old •Grade: <?php echo htmlspecialchars($person->gradefullname); ?>
                                                        )
                                                    <?php else: ?>
                                                        (<span style="color: #20c997;">in <?php echo $daysDifference; ?> day<?php echo $daysDifference > 1 ? 's' : ''; ?></span> • <?php echo $age+1; ?> years old • Grade: <?php echo htmlspecialchars($person->gradefullname); ?>

                                                    <?php endif; ?>
                                                </div>

                                            </div>
                                            <div style="font-size: 28px; color: #339af0;">🎈</div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="no-birthdays animate__animated animate__fadeIn" style="background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%); padding: 15px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); text-align: center; border-left: 5px solid #adb5bd;">
                                        <span style="font-size: 1.1em; color: #6c757d;">🎊 No upcoming birthdays this week 🎊</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- This Month's Birthdays -->
                        <div class="birthday-section animate__animated animate__fadeIn" style="margin-top: 30px;">
                            <div class="section-header" style="background: linear-gradient(135deg, #63e6be 0%, #20c997 100%); color: white; padding: 12px; border-radius: 8px; margin-bottom: 15px; box-shadow: 0 3px 10px rgba(0,0,0,0.1);">
                                <h4 style="margin: 0; font-weight: 600;">📆 Later This Month (<?php echo date('F'); ?>)</h4>
                            </div>
                            <div class="birthday-list">
                                <?php if (count($studentMonthBirthdays) > 0): ?>
                                    <?php foreach ($studentMonthBirthdays as $person): 
                                        $birthDate = new DateTime($person->dateofbirth);
                                        $age = $current_year - $birthDate->format('Y');
                                        $upcomingDate = new DateTime($current_year . '-' . $birthDate->format('m-d'));
                                        $diff = $upcomingDate->diff($today);
                                        $daysDifference = $diff->days;
                                        $isPast = $upcomingDate < $today;
                                    ?>
                                        <div class="birthday-item animate__animated animate__fadeInUp" style="background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%); padding: 15px; margin-bottom: 12px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); display: flex; align-items: center; border-left: 5px solid #63e6be;">
                                            <div style="font-size: 28px; margin-right: 15px; color: #20c997;">✨</div>
                                            <div style="flex-grow: 1;">
                                                <strong style="font-size: 1.1em; color: #495057;"><?php echo htmlentities($person->studentname); ?></strong>
                                                <div style="color: #6c757d; font-size: 0.9em;">
                                                    <?php echo $birthDate->format('F j'); ?> 
                                                    <?php if($isPast): ?>
                                                        (<span style="color: #f06595;"><?php echo $daysDifference; ?> day<?php echo $daysDifference > 1 ? 's' : ''; ?> ago</span> • <?php echo $age; ?> years old • Latest Grade: <?php echo htmlspecialchars($person->gradefullname); ?>
                                                        )
                                                    <?php else: ?>
                                                        (<span style="color: #20c997;">in <?php echo $daysDifference; ?> day<?php echo $daysDifference > 1 ? 's' : ''; ?></span> • <?php echo $age+1; ?> years old • Latest Grade: <?php echo htmlspecialchars($person->gradefullname); ?>
                                                        )
                                                    <?php endif; ?>
                                                </div>

                                            </div>
                                            <div style="font-size: 28px; color: #12b886;">🎁</div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="no-birthdays animate__animated animate__fadeIn" style="background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%); padding: 15px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); text-align: center; border-left: 5px solid #adb5bd;">
                                        <span style="font-size: 1.1em; color: #6c757d;">🎀 No more birthdays this month 🎀</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>          
           
        </div>
    </div>
</div>

<!-- Add these CSS and JS for animations and effects -->

<style>
    @keyframes confetti {
        0% { transform: translateY(0) rotate(0deg); opacity: 1; }
        100% { transform: translateY(100vh) rotate(720deg); opacity: 0; }
    }
    
    .confetti {
        position: relative;
        width: 100%;
        height: 10px;
        margin: 10px 0;
    }
    
    .confetti div {
        position: absolute;
        width: 8px;
        height: 8px;
        background-color: #f00;
        opacity: 0;
        animation: confetti 3s ease-in infinite;
    }
    
    .birthday-item {
        transition: all 0.3s ease;
    }
    
    .birthday-item:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1) !important;
    }
    
    .section-header {
        transition: all 0.3s ease;
    }
    
    .section-header:hover {
        transform: translateX(5px);
    }
    
    /* Fix for modal closing */
    .modal.fade .modal-dialog {
        transition: transform 0.3s ease-out;
    }
    
    /* Ensure close button works */
    .close {
        font-size: 1.8rem;
        line-height: 1;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .modal-lg {
            width: 95%;
            margin: 10px auto;
        }
        
        .birthday-item {
            padding: 10px !important;
        }
        
        .birthday-item div {
            font-size: 24px !important;
            margin-right: 10px !important;
        }
    }
</style>

<script>
    // Add confetti elements dynamically
    document.addEventListener('DOMContentLoaded', function() {
        const confettiContainer = document.querySelector('.confetti');
        if (confettiContainer) {
            // Clear any existing confetti
            confettiContainer.innerHTML = '';
            
            // Add new confetti
            const colors = ['#ff6b6b', '#4dabf7', '#20c997', '#fcc419', '#d57eeb'];
            for (let i = 0; i < 15; i++) {
                const confetti = document.createElement('div');
                confetti.style.left = Math.random() * 100 + '%';
                confetti.style.animationDelay = Math.random() * 2 + 's';
                confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
                confetti.style.width = Math.random() * 6 + 4 + 'px';
                confetti.style.height = confetti.style.width;
                confettiContainer.appendChild(confetti);
            }
        }
        
        // Ensure modal closes properly
        $('#viewbirthdays').on('hidden.bs.modal', function () {
            $(this).removeData('bs.modal');
        });
    });
</script>