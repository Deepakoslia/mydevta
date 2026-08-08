/**
 * DEVTA — Frontend Interactions
 * Hostinger-safe paths + Request Callback modal
 */

(function () {
  "use strict";

  /** Resolve /backend/*.php from /frontend/*.html (also works in subfolders) */
  function backendUrl(file) {
    try {
      var path = window.location.pathname || "/";
      var dir = path.replace(/\/[^/]*$/, "/");
      if (dir.indexOf("/frontend/") !== -1) {
        dir = dir.replace(/\/frontend\/?$/, "/");
      }
      return dir + "backend/" + file;
    } catch (e) {
      return "../backend/" + file;
    }
  }

  async function postForm(url, formData) {
    var res = await fetch(url, {
      method: "POST",
      body: formData,
      headers: { Accept: "application/json" },
      credentials: "same-origin",
    });
    var raw = await res.text();
    try {
      return JSON.parse(raw);
    } catch (err) {
      throw new Error(
        "Server error (not JSON). Check backend/config.php MySQL settings & re-upload backend folder."
      );
    }
  }

  /* ---------- Page Loader ---------- */
  function hideLoader() {
    var loader = document.querySelector(".page-loader");
    if (loader) loader.classList.add("hidden");
  }
  window.addEventListener("load", function () {
    setTimeout(hideLoader, 300);
  });
  // Fallback if some asset never finishes loading
  setTimeout(hideLoader, 2500);

  /* ---------- Sticky Navbar ---------- */
  var navbar = document.querySelector(".navbar");
  function onScroll() {
    if (!navbar) return;
    navbar.classList.toggle("scrolled", window.scrollY > 24);
  }
  window.addEventListener("scroll", onScroll, { passive: true });
  onScroll();

  /* ---------- Mobile Nav ---------- */
  var toggle = document.querySelector(".nav-toggle");
  var links = document.querySelector(".nav-links");

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
  document.addEventListener("click", function (e) {
    var btn = e.target.closest(".btn");
    if (!btn || btn.classList.contains("btn-quote")) return;
    var rect = btn.getBoundingClientRect();
    var ripple = document.createElement("span");
    var size = Math.max(rect.width, rect.height);
    ripple.className = "ripple";
    ripple.style.width = ripple.style.height = size + "px";
    ripple.style.left = e.clientX - rect.left - size / 2 + "px";
    ripple.style.top = e.clientY - rect.top - size / 2 + "px";
    btn.appendChild(ripple);
    setTimeout(function () {
      ripple.remove();
    }, 600);
  });

  /* ---------- Scroll Reveal ---------- */
  var revealEls = document.querySelectorAll(".reveal, .reveal-left, .reveal-right");

  if ("IntersectionObserver" in window) {
    var observer = new IntersectionObserver(
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
      var id = anchor.getAttribute("href");
      if (!id || id === "#") return;
      var target = document.querySelector(id);
      if (!target) return;
      e.preventDefault();
      target.scrollIntoView({ behavior: "smooth", block: "start" });
    });
  });

  /* ---------- Contact Form ---------- */
  var form = document.getElementById("contactForm");
  if (form) {
    form.addEventListener("submit", async function (e) {
      e.preventDefault();
      var status = document.getElementById("formStatus");
      var submitBtn = form.querySelector('[type="submit"]');
      var formData = new FormData(form);

      if (status) {
        status.className = "form-status";
        status.textContent = "Sending...";
      }
      if (submitBtn) submitBtn.disabled = true;

      try {
        var data = await postForm(backendUrl("contact.php"), formData);
        if (status) {
          status.className = "form-status " + (data.success ? "success" : "error");
          status.textContent = data.message || (data.success ? "Sent!" : "Failed.");
        }
        if (data.success) form.reset();
      } catch (err) {
        if (status) {
          status.className = "form-status error";
          status.textContent = err.message || "Network error. Please try again.";
        }
      } finally {
        if (submitBtn) submitBtn.disabled = false;
      }
    });
  }

  /* ---------- Request Callback Modal ---------- */
  var quoteModal = document.getElementById("quoteModal");
  var quoteForm = document.getElementById("quoteForm");
  var quoteService = document.getElementById("quoteService");
  var quoteServiceLabel = document.getElementById("quoteServiceLabel");
  var quoteStatus = document.getElementById("quoteStatus");
  var quoteSubmit = document.getElementById("quoteSubmit");
  var quoteClose = document.getElementById("quoteModalClose");

  function openQuoteModal(serviceName) {
    if (!quoteModal) {
      alert("Callback form missing. Please re-upload frontend/index.html and services.html");
      return;
    }
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
    var nameInput = document.getElementById("quoteName");
    if (nameInput) setTimeout(function () { nameInput.focus(); }, 120);
    if (typeof lucide !== "undefined") lucide.createIcons();
  }

  function closeQuoteModal() {
    if (!quoteModal) return;
    quoteModal.classList.remove("open");
    quoteModal.setAttribute("aria-hidden", "true");
    document.body.classList.remove("modal-open");
  }

  // Event delegation — works even if buttons are re-rendered
  document.addEventListener("click", function (e) {
    var btn = e.target.closest(".btn-quote");
    if (!btn) return;
    e.preventDefault();
    e.stopPropagation();
    openQuoteModal(btn.getAttribute("data-service") || "General Inquiry");
  });

  if (quoteClose) {
    quoteClose.addEventListener("click", function (e) {
      e.preventDefault();
      closeQuoteModal();
    });
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

      var name = (document.getElementById("quoteName") || {}).value || "";
      var email = (document.getElementById("quoteEmail") || {}).value || "";
      var phone = (document.getElementById("quotePhone") || {}).value || "";
      var service = (quoteService && quoteService.value) || "";

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

      var phoneDigits = phone.replace(/[\s\-()]/g, "");
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
        var formData = new FormData(quoteForm);
        formData.set("service", service);
        var data = await postForm(backendUrl("service-request.php"), formData);

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
    try {
      lucide.createIcons();
    } catch (e) {}
  }
})();
