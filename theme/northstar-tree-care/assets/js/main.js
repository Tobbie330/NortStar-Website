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

  // Gallery lightbox.
  var lightbox = document.getElementById("lightbox");
  if (lightbox) {
    var lbImg = document.getElementById("lightbox-img");
    var lbCap = document.getElementById("lightbox-cap");
    var closeBtn = lightbox.querySelector(".lightbox-close");

    function openLightbox(src, caption) {
      lbImg.setAttribute("src", src);
      lbImg.setAttribute("alt", caption || "");
      lbCap.textContent = caption || "";
      lightbox.classList.add("is-open");
      lightbox.setAttribute("aria-hidden", "false");
    }
    function closeLightbox() {
      lightbox.classList.remove("is-open");
      lightbox.setAttribute("aria-hidden", "true");
    }

    document.querySelectorAll(".gallery-link").forEach(function (link) {
      link.addEventListener("click", function (e) {
        e.preventDefault();
        openLightbox(link.getAttribute("href"), link.getAttribute("data-caption"));
      });
    });
    closeBtn.addEventListener("click", closeLightbox);
    lightbox.addEventListener("click", function (e) {
      if (e.target === lightbox) closeLightbox();
    });
    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape") closeLightbox();
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
