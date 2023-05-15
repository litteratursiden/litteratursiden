// ui-accordion-header-active ui-state-active
let elements = document.querySelectorAll(".custom-accordion");

[...elements].forEach(el => {
    el.addEventListener("click", function(e) {
        this.classList.toggle("ui-accordion-header-active");
        this.classList.toggle("ui-state-active");
    }, false);
})