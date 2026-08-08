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

  /* ---------- Service Quote Modal ---------- */
  const quoteModal = document.getElementById("quoteModal");
  const quoteForm = document.getElementById("quoteForm");
  const quoteService = document.getElementById("quoteService");
  const quoteServiceLabel = document.getElementById("quoteServiceLabel");
  const quoteStatus = document.getElementById("quoteStatus");
  const quoteSubmit = document.getElementById("quoteSubmit");
  const quoteClose = document.getElementById("quoteModalClose");

  function openQuoteModal(serviceName) {
    if (!quoteModal) return;
    if (quoteService) quoteService.value = serviceName;
    if (quoteServiceLabel) quoteServiceLabel.textContent = serviceName;
    if (quoteStatus) {
      quoteStatus.className = "form-status";
      quoteStatus.textContent = "";
    }
    if (quoteForm) quoteForm.reset();
    if (quoteService) quoteService.value = serviceName;
    quoteModal.classList.add("open");
    quoteModal.setAttribute("aria-hidden", "false");
    document.body.classList.add("modal-open");
    const nameInput = document.getElementById("quoteName");
    if (nameInput) setTimeout(function () { nameInput.focus(); }, 120);
    if (typeof lucide !== "undefined") lucide.createIcons();
  }

  function closeQuoteModal() {
    if (!quoteModal) return;
    quoteModal.classList.remove("open");
    quoteModal.setAttribute("aria-hidden", "true");
    document.body.classList.remove("modal-open");
  }

  document.querySelectorAll(".btn-quote").forEach(function (btn) {
    btn.addEventListener("click", function (e) {
      e.preventDefault();
      e.stopPropagation();
      openQuoteModal(btn.getAttribute("data-service") || "General Inquiry");
    });
  });

  if (quoteClose) {
    quoteClose.addEventListener("click", closeQuoteModal);
  }

  if (quoteModal) {
    quoteModal.addEventListener("click", function (e) {
      if (e.target === quoteModal) closeQuoteModal();
    });
  }

  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape" && quoteModal && quoteModal.classList.contains("open")) {
      closeQuoteModal();
    }
  });

  if (quoteForm) {
    quoteForm.addEventListener("submit", async function (e) {
      e.preventDefault();

      const name = (document.getElementById("quoteName") || {}).value || "";
      const email = (document.getElementById("quoteEmail") || {}).value || "";
      const phone = (document.getElementById("quotePhone") || {}).value || "";
      const service = (quoteService && quoteService.value) || "";

      if (!name.trim() || !email.trim() || !phone.trim() || !service.trim()) {
        if (quoteStatus) {
          quoteStatus.className = "form-status error";
          quoteStatus.textContent = "Please fill in all required fields.";
        }
        return;
      }

      if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.trim())) {
        if (quoteStatus) {
          quoteStatus.className = "form-status error";
          quoteStatus.textContent = "Please enter a valid email address.";
        }
        return;
      }

      const phoneDigits = phone.replace(/[\s\-()]/g, "");
      if (!/^\+?[0-9]{10,15}$/.test(phoneDigits)) {
        if (quoteStatus) {
          quoteStatus.className = "form-status error";
          quoteStatus.textContent = "Please enter a valid phone number (10–15 digits).";
        }
        return;
      }

      if (quoteStatus) {
        quoteStatus.className = "form-status";
        quoteStatus.textContent = "";
      }
      if (quoteSubmit) {
        quoteSubmit.disabled = true;
        quoteSubmit.classList.add("btn-loading");
      }

      try {
        const formData = new FormData(quoteForm);
        formData.set("service", service);

        const res = await fetch("../backend/service-request.php", {
          method: "POST",
          body: formData,
          headers: { Accept: "application/json" },
        });

        const raw = await res.text();
        let data;
        try {
          data = JSON.parse(raw);
        } catch (parseErr) {
          throw new Error(
            "Server did not return JSON. Start the site with start-server.bat (PHP), not a static file open."
          );
        }

        if (quoteStatus) {
          quoteStatus.className = "form-status " + (data.success ? "success" : "error");
          quoteStatus.textContent = data.message || (data.success ? "Submitted!" : "Failed.");
        }

        if (data.success) {
          quoteForm.reset();
          if (quoteService) quoteService.value = service;
          if (quoteServiceLabel) quoteServiceLabel.textContent = service;
          setTimeout(closeQuoteModal, 1800);
        }
      } catch (err) {
        if (quoteStatus) {
          quoteStatus.className = "form-status error";
          quoteStatus.textContent = err.message || "Network error. Please try again.";
        }
      } finally {
        if (quoteSubmit) {
          quoteSubmit.disabled = false;
          quoteSubmit.classList.remove("btn-loading");
        }
      }
    });
  }

  /* ---------- Lucide Icons ---------- */
  if (typeof lucide !== "undefined") {
    lucide.createIcons();
  }
})();
