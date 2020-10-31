// Initialize highlight.js if a the current page contains `pre` tags
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
