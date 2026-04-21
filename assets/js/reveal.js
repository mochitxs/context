/**
 * Revela elementos al entrar en viewport con una animación suave.
 *
 * La intención es acercar la sensación editorial de carga progresiva:
 * los bloques no aparecen todos de golpe, sino que se presentan conforme
 * el usuario recorre la página.
 */
(function initializeRevealOnScroll() {
    const selectors = [
        ".featured-article-shell",
        ".featured-song-shell",
        ".article-section-card",
        ".post-card",
        ".post-view-card",
        ".section-card",
        ".auth-card",
        ".create-card",
        ".settings-shell",
        ".settings-content-card",
        ".settings-comment-card",
        ".settings-notification-card",
        ".main-footer"
    ];

    const revealTargets = document.querySelectorAll(selectors.join(","));

    revealTargets.forEach((element, index) => {
        element.classList.add("reveal-on-scroll");
        element.style.setProperty("--reveal-delay", `${Math.min(index % 6, 5) * 70}ms`);
    });

    if (!("IntersectionObserver" in window)) {
        revealTargets.forEach((element) => element.classList.add("is-visible"));
        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) {
                    return;
                }

                entry.target.classList.add("is-visible");
                observer.unobserve(entry.target);
            });
        },
        {
            threshold: 0.14,
            rootMargin: "0px 0px -40px 0px"
        }
    );

    revealTargets.forEach((element) => observer.observe(element));
})();
