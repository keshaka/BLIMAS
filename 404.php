<?php $page_title = "Page Not Found - BLIMAS"; ?>
<?php include 'includes/header.php'; ?>

<main class="main-content">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 text-center">
                <div class="error-page" data-aos="fade-up">
                    <div class="error-icon">
                        <i class="fas fa-exclamation-triangle fa-5x text-warning"></i>
                    </div>
                    <h1 class="display-1 text-primary">404</h1>
                    <h2 class="mb-4">Page Not Found</h2>
                    <p class="lead mb-4">The page you're looking for doesn't exist or has been moved.</p>
                    <a href="index.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-home me-2"></i>Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</main>

<style>
.error-page {
    padding: 3rem 0;
}
.error-icon {
    margin-bottom: 2rem;
}
</style>

<?php include 'includes/footer.php'; ?>