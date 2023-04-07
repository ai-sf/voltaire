
htmx.on("showToast", function (event) {
  var toastInstance = new bootstrap.Toast(toast);
  toastInstance.show();
});

htmx.config.useTemplateFragments = true;


function showToast(message) {
  const liveToast = document.querySelector('#errortoast');
  liveToast.querySelector(".toast-body").innerHTML = message;
  const toast = new bootstrap.Toast(liveToast);
  toast.show();
}



