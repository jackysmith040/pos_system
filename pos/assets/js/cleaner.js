document.addEventListener("DOMContentLoaded", function() {
    // Check if the element is present in the DOM
    const unwantedElement = document.querySelector("div[style*='color: rgb(156, 159, 166);']");
    
    if (unwantedElement) {
        unwantedElement.remove();
    }

    // Optionally, you can also observe DOM changes in case the script injects the element later
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            const unwantedElement = document.querySelector("div[style*='color: rgb(156, 159, 166);']");
            if (unwantedElement) {
                unwantedElement.remove();
            }
        });
    });

    // Start observing the body for child node additions
    observer.observe(document.body, { childList: true, subtree: true });
});
