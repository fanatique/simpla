document.querySelector('.header__nav-button').addEventListener('click', function () {
  document.querySelector('.main-nav').classList.toggle('main-nav-show');
  this.querySelector('.header__nav-icon').classList.toggle('header__nav-icon--show');
  document.querySelector('body').classList.toggle('body--no-scroll');
});


if (document.querySelectorAll('pre').length > 0) {
  const head = document.getElementsByTagName('head')[0];
  const link = document.createElement('link');
  link.rel = 'stylesheet';
  link.type = 'text/css';
  link.href = '/css/styles/default.css';
  link.media = 'screen';
  head.appendChild(link);

  const script = document.createElement('script');
  script.setAttribute('src', '/js/highlight.pack.js');
  script.onload = function () {
    hljs.initHighlightingOnLoad();
  };
  head.appendChild(script);
}