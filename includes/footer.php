        </main> <!-- .main-content -->

        <footer class="site-footer">
            <div class="container">
                <div class="row">
                    <div class="col-md-8">
                        <form action="#" class="subscribe-form">
                            <input type="text" placeholder="Enter your email to subscribe...">
                            <input type="submit" value="Subscribe">
                        </form>
                    </div>
                    <div class="col-md-3 col-md-offset-1">
                        <div class="social-links">
                            <a href="#"><i class="fa fa-facebook"></i></a>
                            <a href="#"><i class="fa fa-twitter"></i></a>
                            <a href="#"><i class="fa fa-google-plus"></i></a>
                            <a href="#"><i class="fa fa-pinterest"></i></a>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-12">
                        <p class="colophon">
                            Copyright <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved. 
                            <span style="float: right; font-size: 12px;">
                                <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/admin/login.php" 
                                   style="color: #666; text-decoration: none;" 
                                   title="Administrator Login">Admin</a>
                            </span>
                        </p>
                    </div>
                </div>
            </div>
        </footer> <!-- .site-footer -->
    </div> <!-- .site-content -->

    <!-- JavaScript -->
    <script src="<?php echo isset($base_url) ? $base_url : ''; ?>/js/jquery-1.11.1.min.js"></script>
    <script src="<?php echo isset($base_url) ? $base_url : ''; ?>/js/plugins.js"></script>
    <script src="<?php echo isset($base_url) ? $base_url : ''; ?>/js/app.js"></script>
    <script src="<?php echo isset($base_url) ? $base_url : ''; ?>/assets/js/main.js"></script>
    <script src="<?php echo isset($base_url) ? $base_url : ''; ?>/assets/js/charts.js"></script>
    
    <?php if (isset($additional_js)): ?>
        <?php foreach ($additional_js as $js): ?>
            <script src="<?php echo $js; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>

</body>
</html>