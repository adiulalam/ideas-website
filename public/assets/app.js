/*
 * Progressive enhancement only — every feature works without JavaScript.
 * No inline handlers anywhere: the Content-Security-Policy forbids them.
 */
(() => {
  'use strict';

  // Mobile navigation toggle.
  document.querySelectorAll('[data-nav-toggle]').forEach((button) => {
    const menu = document.getElementById(button.getAttribute('aria-controls') || '');
    if (!menu) return;
    button.addEventListener('click', () => {
      const open = button.getAttribute('aria-expanded') === 'true';
      button.setAttribute('aria-expanded', String(!open));
      menu.classList.toggle('hidden', open);
    });
  });

  // Submit a form as soon as a <select data-autosubmit> changes.
  document.querySelectorAll('select[data-autosubmit]').forEach((select) => {
    select.addEventListener('change', () => {
      if (select.form) select.form.submit();
    });
  });

  // Confirmation dialog for destructive forms: <form data-confirm="Are you sure?">.
  const dialog = document.getElementById('confirm-dialog');
  let pendingForm = null;

  document.addEventListener('submit', (event) => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement) || !form.dataset.confirm) return;

    event.preventDefault();
    if (!dialog || typeof dialog.showModal !== 'function') {
      if (window.confirm(form.dataset.confirm)) form.submit();
      return;
    }

    pendingForm = form;
    const message = dialog.querySelector('[data-confirm-message]');
    if (message) message.textContent = form.dataset.confirm;
    dialog.returnValue = '';
    dialog.showModal();
  });

  if (dialog) {
    dialog.addEventListener('close', () => {
      // form.submit() does not fire another submit event, so no loop.
      if (dialog.returnValue === 'confirm' && pendingForm) pendingForm.submit();
      pendingForm = null;
    });
  }

  // Dismissable flash messages.
  document.addEventListener('click', (event) => {
    const button = event.target instanceof Element ? event.target.closest('[data-dismiss]') : null;
    if (button) button.closest('[data-flash]')?.remove();
  });
})();
