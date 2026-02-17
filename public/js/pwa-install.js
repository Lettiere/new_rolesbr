(function () {
  if ("serviceWorker" in navigator) {
    window.addEventListener("load", function () {
      navigator.serviceWorker.register("/service-worker.js").catch(function () {});
    });
  }

  var deferredPrompt = null;
  var installBar = document.getElementById("pwaInstallBar");
  var installBtn = document.getElementById("pwaInstallBtn");
  var installClose = document.getElementById("pwaInstallClose");

  function isStandalone() {
    var mq = window.matchMedia && window.matchMedia("(display-mode: standalone)").matches;
    var iosStandalone = window.navigator.standalone === true;
    return mq || iosStandalone;
  }

  function shouldShowInstallBar() {
    if (!installBar) return false;
    if (isStandalone()) return false;
    if (localStorage.getItem("rolesbr_pwa_installed") === "1") return false;
    if (localStorage.getItem("rolesbr_pwa_dismissed") === "1") return false;
    var isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|Windows Phone/i.test(navigator.userAgent || "");
    return isMobile;
  }

  window.addEventListener("beforeinstallprompt", function (e) {
    e.preventDefault();
    deferredPrompt = e;
    if (shouldShowInstallBar()) {
      installBar.classList.remove("d-none");
    }
  });

  window.addEventListener("appinstalled", function () {
    localStorage.setItem("rolesbr_pwa_installed", "1");
    if (installBar) {
      installBar.classList.add("d-none");
    }
  });

  if (installBtn) {
    installBtn.addEventListener("click", function () {
      if (deferredPrompt) {
        deferredPrompt.prompt();
        deferredPrompt.userChoice.then(function (choice) {
          if (choice.outcome === "accepted") {
            localStorage.setItem("rolesbr_pwa_installed", "1");
          }
          deferredPrompt = null;
          if (installBar) {
            installBar.classList.add("d-none");
          }
        });
      } else if (!isStandalone()) {
        alert("Para instalar, use a opção de adicionar à tela inicial do seu navegador.");
      }
    });
  }

  if (installClose) {
    installClose.addEventListener("click", function () {
      localStorage.setItem("rolesbr_pwa_dismissed", "1");
      if (installBar) {
        installBar.classList.add("d-none");
      }
    });
  }

  if (installBar && shouldShowInstallBar()) {
    if (!deferredPrompt) {
      installBar.classList.remove("d-none");
    }
  }

  var shareButton = document.getElementById("btnShareSite");
  if (shareButton) {
    shareButton.addEventListener("click", function (event) {
      event.preventDefault();
      var shareData = {
        title: document.title || "RolesBr",
        text: "Confere os rolês no RolesBr",
        url: window.location.origin + "/"
      };
      if (navigator.share) {
        navigator.share(shareData).catch(function () {});
      } else if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(shareData.url).then(function () {
          alert("Link copiado para a área de transferência.");
        }).catch(function () {
          alert("Não foi possível copiar o link. Use o menu de compartilhamento do navegador.");
        });
      } else {
        var link = shareData.url;
        window.prompt("Copie o link do site:", link);
      }
    });
  }
})();*** End Patch ***!
