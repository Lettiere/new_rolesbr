(function () {
  if ('serviceWorker' in navigator) {
    window.addEventListener('load', function () {
      navigator.serviceWorker.register('/service-worker.js').catch(function () {});
    });
  }
  var shareButton = document.getElementById('btnShareSite');
  if (shareButton) {
    shareButton.addEventListener('click', function (event) {
      event.preventDefault();
      var shareData = {
        title: document.title || 'RolesBr',
        text: 'Confere os rolês no RolesBr',
        url: window.location.origin + '/'
      };
      if (navigator.share) {
        navigator.share(shareData).catch(function () {});
      } else if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(shareData.url).then(function () {
          alert('Link copiado para a área de transferência.');
        }).catch(function () {
          alert('Não foi possível copiar o link. Use o menu de compartilhamento do navegador.');
        });
      } else {
        var link = shareData.url;
        window.prompt('Copie o link do site:', link);
      }
    });
  }
})();

