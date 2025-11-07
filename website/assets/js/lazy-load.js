/**
 * Lazy Load Images
 * Loads blur image first, then loads the full/thumb image when visible
 */
document.addEventListener('DOMContentLoaded', function () {
    // Get all lazy load images
    const lazyImages = document.querySelectorAll('img.lazy-load');

    // If browser supports IntersectionObserver
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver(function (entries, observer) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    const src = img.getAttribute('data-src');

                    if (src) {
                        // Create a new image to preload
                        const tempImg = new Image();
                        tempImg.onload = function () {
                            // Once loaded, swap the src
                            img.src = src;
                            img.classList.add('loaded');
                        };
                        tempImg.src = src;
                    }

                    // Stop observing this image
                    imageObserver.unobserve(img);
                }
            });
        }, {
            // Load images 50px before they enter the viewport
            rootMargin: '50px'
        });

        // Observe all lazy images
        lazyImages.forEach(function (img) {
            imageObserver.observe(img);
        });
    } else {
        // Fallback for browsers that don't support IntersectionObserver
        lazyImages.forEach(function (img) {
            const src = img.getAttribute('data-src');
            if (src) {
                img.src = src;
            }
        });
    }
});
