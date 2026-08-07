/**
 * DEVTA — Frontend Interactions
 * Minimal, production-ready vanilla JS
 */

(function () {
  "use strict";

  /* ---------- Page Loader ---------- */
  window.addEventListener("load", function () {
    const loader = document.querySelector(".page-loader");
    if (loader) {
      setTimeout(function () {
        loader.classList.add("hidden");
      }, 400);
    }
  });

  /* ---------- Sticky Navbar ---------- */
  const navbar = document.querySelector(".navbar");
  function onScroll() {
    if (!navbar) return;
    navbar.classList.toggle("scrolled", window.scrollY > 24);
  }
  window.addEventListener("scroll", onScroll, { passive: true });
  onScroll();

  /* ---------- Mobile Nav ---------- */
  const toggle = document.querySelector(".nav-toggle");
  const links = document.querySelector(".nav-links");

  if (toggle && links) {
    toggle.addEventListener("click", function () {
      toggle.classList.toggle("active");
      links.classList.toggle("open");
    });

    links.querySelectorAll("a").forEach(function (link) {
      link.addEventListener("click", function () {
        toggle.classList.remove("active");
        links.classList.remove("open");
      });
    });
  }

  /* ---------- Button Ripple ---------- */
  document.querySelectorAll(".btn").forEach(function (btn) {
    btn.addEventListener("click", function (e) {
      const rect = btn.getBoundingClientRect();
      const ripple = document.createElement("span");
      const size = Math.max(rect.width, rect.height);
      ripple.className = "ripple";
      ripple.style.width = ripple.style.height = size + "px";
      ripple.style.left = e.clientX - rect.left - size / 2 + "px";
      ripple.style.top = e.clientY - rect.top - size / 2 + "px";
      btn.appendChild(ripple);
      setTimeout(function () {
        ripple.remove();
      }, 600);
    });
  });

  /* ---------- Scroll Reveal ---------- */
  const revealEls = document.querySelectorAll(".reveal, .reveal-left, .reveal-right");

  if ("IntersectionObserver" in window) {
    const observer = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            entry.target.classList.add("visible");
            observer.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.12, rootMargin: "0px 0px -40px 0px" }
    );

    revealEls.forEach(function (el) {
      observer.observe(el);
    });
  } else {
    revealEls.forEach(function (el) {
      el.classList.add("visible");
    });
  }

  /* ---------- Smooth Anchor Scroll ---------- */
  document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
    anchor.addEventListener("click", function (e) {
      const id = anchor.getAttribute("href");
      if (!id || id === "#") return;
      const target = document.querySelector(id);
      if (!target) return;
      e.preventDefault();
      target.scrollIntoView({ behavior: "smooth", block: "start" });
    });
  });

  /* ---------- Contact Form ---------- */
  const form = document.getElementById("contactForm");
  if (form) {
    form.addEventListener("submit", async function (e) {
      e.preventDefault();
      const status = document.getElementById("formStatus");
      const submitBtn = form.querySelector('[type="submit"]');
      const formData = new FormData(form);

      if (status) {
        status.className = "form-status";
        status.textContent = "Sending...";
      }
      if (submitBtn) submitBtn.disabled = true;

      try {
        const res = await fetch("../backend/contact.php", {
          method: "POST",
          body: formData,
        });
        const data = await res.json();

        if (status) {
          status.className = "form-status " + (data.success ? "success" : "error");
          status.textContent = data.message || (data.success ? "Sent!" : "Failed.");
        }

        if (data.success) form.reset();
      } catch (err) {
        if (status) {
          status.className = "form-status error";
          status.textContent = "Network error. Please try again.";
        }
      } finally {
        if (submitBtn) submitBtn.disabled = false;
      }
    });
  }

  /* ---------- Lucide Icons ---------- */
  if (typeof lucide !== "undefined") {
    lucide.createIcons();
  }
})();
