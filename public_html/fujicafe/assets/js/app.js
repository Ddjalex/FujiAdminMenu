const translations = {
  en: {
    menu: 'Menu',
    feedback: 'Feedback',
    contact: 'Contact Us',
    review: 'Review',
    subtitle: 'Artisan Coffee & Fresh Cuisine',
    all_items: 'All Items',
    search_placeholder: 'Search menu...',
    reviews_btn: 'Reviews',
    no_menu_items: 'No menu items available at the moment.',
    feedback_title: 'Feedback',
    feedback_desc: 'We value your feedback! Let us know how we can improve your experience.',
    your_name: 'Your Name',
    name_placeholder: 'Enter your name',
    email_address: 'Email Address',
    email_placeholder: 'your@email.com',
    your_feedback_label: 'Your Feedback',
    feedback_placeholder: 'Share your thoughts with us...',
    submit_feedback: 'Submit Feedback',
    contact_title: 'Contact Us',
    location: '📍 Location',
    phone: '📞 Phone',
    hours: '⏰ Hours',
    hours_detail: 'Mon-Fri: 7:00 AM - 8:00 PM<br>Sat-Sun: 8:00 AM - 9:00 PM',
    email: '✉️ Email',
    leave_review: 'Leave a Review',
    review_desc: 'Share your experience with us and help others discover great items!',
    review_instruction: 'Click the "Reviews" button on any menu item above to leave your feedback.',
    your_rating: 'Your Rating',
    your_name_optional: 'Your Name (optional)',
    anonymous: 'Anonymous',
    your_review: 'Your Review',
    review_placeholder: 'Share your experience...',
    submit_review: 'Submit Review'
  },
  am: {
    menu: 'ምናሌ',
    feedback: 'አስተያየት',
    contact: 'አግኙን',
    review: 'ግምገማ',
    subtitle: 'ባለሙያ ቡና እና ትኩስ ምግብ',
    all_items: 'ሁሉም ምግቦች',
    search_placeholder: 'ምናሌ ፈልግ...',
    reviews_btn: 'ግምገማዎች',
    no_menu_items: 'በአሁኑ ጊዜ ምንም የምናሌ ምግቦች የሉም።',
    feedback_title: 'አስተያየት',
    feedback_desc: 'አስተያየትዎን እናከብራለን! ተሞክሮዎን እንዴት ማሻሻል እንደምንችል ያሳውቁን።',
    your_name: 'ስምዎ',
    name_placeholder: 'ስምዎን ያስገቡ',
    email_address: 'ኢሜይል አድራሻ',
    email_placeholder: 'የእርስዎ@ኢሜይል.com',
    your_feedback_label: 'አስተያየትዎ',
    feedback_placeholder: 'ሀሳብዎን ያካፍሉ...',
    submit_feedback: 'አስተያየት ያስገቡ',
    contact_title: 'አግኙን',
    location: '📍 አድራሻ',
    phone: '📞 ስልክ',
    hours: '⏰ የስራ ሰዓት',
    hours_detail: 'ሰኞ-አርብ: 7:00 ጠዋት - 8:00 ምሽት<br>ቅዳሜ-እሑድ: 8:00 ጠዋት - 9:00 ምሽት',
    email: '✉️ ኢሜይል',
    leave_review: 'ግምገማ ያስገቡ',
    review_desc: 'ተሞክሮዎን ያካፍሉን እና ሌሎች ምርጥ ምግቦችን እንዲያገኙ ይርዱን!',
    review_instruction: 'አስተያየትዎን ለመተው ከላይ በማንኛውም ምናሌ ላይ "ግምገማዎች" የሚለውን ቁልፍ ይጫኑ።',
    your_rating: 'ደረጃዎ',
    your_name_optional: 'ስምዎ (አማራጭ)',
    anonymous: 'ስም አልባ',
    your_review: 'ግምገማዎ',
    review_placeholder: 'ተሞክሮዎን ያካፍሉ...',
    submit_review: 'ግምገማ ያስገቡ'
  }
};

let currentLang = localStorage.getItem('lang') || 'en';

window.switchLanguage = function(lang) {
  currentLang = lang;
  localStorage.setItem('lang', lang);
  
  const btnEn = document.getElementById('btnEn');
  const btnAm = document.getElementById('btnAm');
  if (btnEn && btnAm) {
    document.querySelectorAll('#btnEn, #btnAm').forEach(btn => btn.classList.remove('active'));
    (lang === 'en' ? btnEn : btnAm).classList.add('active');
  }
  
  document.querySelectorAll('[data-translate]').forEach(el => {
    const key = el.getAttribute('data-translate');
    if (translations[lang] && translations[lang][key]) {
      el.innerHTML = translations[lang][key];
    }
  });
  
  document.querySelectorAll('[data-translate-placeholder]').forEach(el => {
    const key = el.getAttribute('data-translate-placeholder');
    if (translations[lang] && translations[lang][key]) {
      el.placeholder = translations[lang][key];
    }
  });
  
  document.querySelectorAll('.cat-text').forEach(el => {
    const btn = el.closest('.pill');
    if (btn) {
      const text = lang === 'am' ? btn.getAttribute('data-cat-am') : btn.getAttribute('data-cat-en');
      if (text) el.textContent = text;
    }
  });
  
  document.querySelectorAll('.cat-title-text').forEach(el => {
    const parent = el.closest('.section-title');
    if (parent) {
      const text = lang === 'am' ? parent.getAttribute('data-cat-am') : parent.getAttribute('data-cat-en');
      if (text) el.textContent = text;
    }
  });
  
  document.querySelectorAll('.item-name-text').forEach(el => {
    const parent = el.closest('.card-title');
    if (parent) {
      const text = lang === 'am' ? parent.getAttribute('data-name-am') : parent.getAttribute('data-name-en');
      if (text) el.textContent = text;
    }
  });
  
  document.querySelectorAll('.item-desc-text').forEach(el => {
    const parent = el.closest('.card-desc');
    if (parent) {
      const text = lang === 'am' ? parent.getAttribute('data-desc-am') : parent.getAttribute('data-desc-en');
      if (text) el.textContent = text;
    }
  });
  
  document.querySelectorAll('.card').forEach(card => {
    const searchText = lang === 'am' ? card.getAttribute('data-item-am') : card.getAttribute('data-item-en');
    if (searchText) card.setAttribute('data-item', searchText);
  });
}

document.addEventListener('DOMContentLoaded', () => {
  window.switchLanguage(currentLang);
  
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
