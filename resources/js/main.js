
htmx.on("showToast", function (event) {
  var toastInstance = new bootstrap.Toast(toast);
  toastInstance.show();
});

htmx.config.useTemplateFragments = true;


function showToast(message) {
  console.log(message);
  const liveToast = document.querySelector('#toast');
  liveToast.querySelector(".toast-body").innerHTML = message;
  const toast = new bootstrap.Toast(liveToast);
  console.log(toast);
  toast.show();
}



