<div aria-hidden="true" aria-labelledby="programmerModalLabel" role="dialog" tabindex="-1" id="programmermyModal" class="modal fade">
    <div class="modal-dialog modal-dialog-centered"> 
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h2 class="modal-title w-100 text-center" style="font-family: 'Trebuchet MS', 'Lucida Sans Unicode', 'Lucida Grande', 'Lucida Sans', Arial, sans-serif;">
                    <i class="fas fa-user-tie me-2"></i>Developer Details
                </h2>
                <button aria-hidden="true" data-dismiss="modal" class="close text-white" type="button">&times;</button>
            </div>
            
            <div class="modal-body p-4">
                <div class="programmer-details">
                    <div class="d-flex align-items-center mb-4">
                        <div class="flex-shrink-0">
                            <i class="fas fa-user-circle fa-3x text-primary"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0">Kiplimo Rodgers</h3>
                            <p class="text-muted mb-0">Full Stack Developer</p>
                        </div>
                    </div>

                    <div class="contact-info">
                        <div class="d-flex align-items-center mb-3">
                            <div class="icon-circle bg-primary text-white me-3">
                                <i class="fas fa-phone-alt"></i>
                            </div>
                            <div>
                                <h5 class="mb-0">Phone Numbers</h5>
                                <div class="d-flex flex-column">
                                    <a href="tel:+254721859015" class="text-decoration-none">+254 721 859015</a>
                                    <a href="tel:+254771595926" class="text-decoration-none">+254 771 595926</a>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex align-items-center mb-3">
                            <div class="icon-circle bg-primary text-white me-3">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div>
                                <h5 class="mb-0">Email Address</h5>
                                <a href="mailto:kiplimos@gmail.com" class="text-decoration-none">kiplimos@gmail.com</a>
                            </div>
                        </div>

                        <div class="social-links mt-4 pt-3 border-top text-center">
                            <a href="#" class="text-primary mx-2"><i class="fab fa-github fa-2x"></i></a>
                            <a href="#" class="text-primary mx-2"><i class="fab fa-linkedin fa-2x"></i></a>
                            <a href="#" class="text-primary mx-2"><i class="fab fa-twitter fa-2x"></i></a>
                            <a href="#" class="text-primary mx-2"><i class="fab fa-whatsapp fa-2x"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .icon-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .programmer-details a {
        color: #0d6efd;
        transition: color 0.3s;
    }
    .programmer-details a:hover {
        color: #0b5ed7;
        text-decoration: underline;
    }
    .social-links a {
        transition: transform 0.3s;
    }
    .social-links a:hover {
        transform: translateY(-3px);
    }
</style>