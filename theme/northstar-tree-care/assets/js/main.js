/* North Star Tree Care — front-end interactions */
(function () {
  "use strict";

  var header = document.getElementById("site-header");
  var toggle = document.getElementById("nav-toggle");
  var nav = document.getElementById("primary-nav");

  // Solid header background once the user scrolls past the hero top.
  function onScroll() {
    if (!header) return;
    if (window.scrollY > 40) {
      header.classList.add("is-solid");
    } else {
      header.classList.remove("is-solid");
    }
  }
  window.addEventListener("scroll", onScroll, { passive: true });
  onScroll();

  // Mobile menu toggle.
  if (toggle && nav) {
    toggle.addEventListener("click", function () {
      var open = nav.classList.toggle("is-open");
      toggle.setAttribute("aria-expanded", open ? "true" : "false");
    });

    // Close the menu after a link is tapped.
    nav.addEventListener("click", function (e) {
      if (e.target.tagName === "A") {
        nav.classList.remove("is-open");
        toggle.setAttribute("aria-expanded", "false");
      }
    });
  }

  // If the form was submitted, scroll to the result message.
  if (window.location.hash === "#contact" && window.location.search.indexOf("quote=") !== -1) {
    var contact = document.getElementById("contact");
    if (contact) {
      window.requestAnimationFrame(function () {
        contact.scrollIntoView();
      });
    }
  }
})();
