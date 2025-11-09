document.addEventListener('DOMContentLoaded', () => {
  const q = document.querySelector('[data-search]');
  if (q) {
    q.addEventListener('input', () => {
      const term = q.value.trim().toLowerCase();
      document.querySelectorAll('[data-item]').forEach(card => {
        const text = card.dataset.item.toLowerCase();
        card.style.display = text.includes(term) ? '' : 'none';
      });
    });
  }
  document.querySelectorAll('[data-cat]').forEach(btn => {
    btn.addEventListener('click', e => {
      e.preventDefault();
      const cat = btn.dataset.cat;
      document.querySelectorAll('[data-cat]').forEach(b=>b.classList.remove('active'));
      btn.classList.add('active');
      document.querySelectorAll('[data-catname]').forEach(el=>{
        el.style.display = (cat==='all' || el.dataset.catname===cat) ? '' : 'none';
      });
    });
  });
});
