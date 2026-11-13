// Small progressive enhancement: mark forms as submitting to prevent accidental double-clicks.
document.querySelectorAll('form').forEach((form) => {
  form.addEventListener('submit', () => {
    const button = form.querySelector('button[type="submit"]');
    if (button && !form.hasAttribute('onsubmit')) {
      button.dataset.originalText = button.textContent;
      button.disabled = true;
      button.textContent = 'Please wait...';
    }
  });
});
