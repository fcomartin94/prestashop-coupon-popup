document.addEventListener('DOMContentLoaded', function () {
  var popup = document.getElementById('couponpopup');
  if (!popup) {
    return;
  }

  // Delay and re-appearance period come from the module configuration
  // (rendered as data attributes by the template).
  function readInt(attribute, fallback) {
    var value = parseInt(popup.getAttribute(attribute), 10);
    return isNaN(value) ? fallback : value;
  }

  var KEY = 'couponpopup_seen';
  var DELAY = readInt('data-delay', 3) * 1000; // milliseconds before showing the popup
  var REAPPEAR = readInt('data-reappear-days', 2) * 24 * 60 * 60 * 1000; // days in milliseconds

  // Returns true if the popup must not be shown (visitor clicked "Sign up",
  // or closed it less than REAPPEAR milliseconds ago).
  function alreadySeen() {
    try {
      var value = localStorage.getItem(KEY);
      if (!value) {
        return false;
      }
      if (value === 'registered') {
        return true;
      }
      var closedAt = parseInt(value, 10);
      if (isNaN(closedAt)) {
        return false;
      }
      return (Date.now() - closedAt) < REAPPEAR;
    } catch (e) {
      return false;
    }
  }

  function markClosed() {
    try {
      localStorage.setItem(KEY, String(Date.now()));
    } catch (e) {}
  }

  function markRegistered() {
    try {
      localStorage.setItem(KEY, 'registered');
    } catch (e) {}
  }

  function isVisible() {
    return popup.style.display === 'flex';
  }

  function closePopup() {
    popup.style.display = 'none';
    markClosed();
  }

  if (alreadySeen()) {
    return;
  }

  popup.querySelector('.couponpopup__close').addEventListener('click', closePopup);
  popup.querySelector('.couponpopup__decline').addEventListener('click', closePopup);

  popup.querySelector('.couponpopup__button').addEventListener('click', markRegistered);

  popup.addEventListener('click', function (e) {
    if (e.target === popup) {
      closePopup();
    }
  });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && isVisible()) {
      closePopup();
    }
  });

  setTimeout(function () {
    if (!alreadySeen()) {
      popup.style.display = 'flex';
    }
  }, DELAY);
});
