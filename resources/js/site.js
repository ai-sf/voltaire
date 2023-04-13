 // Define Alpine.js methods
const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));



 function handleKeyDown(event, pollId){
  var inputField = event.target;
  var key = event.key;
  var index = parseInt(inputField.id.slice(inputField.id.indexOf("__") + 2));
  console.log(index);
  switch (key) {
    case "ArrowRight":
      focusNextInput(inputField, pollId, index);
      break;
    case "ArrowLeft":
      focusPreviousInput(inputField, pollId, index);
      break;

    case "Backspace":
      deleteInputAndBack(inputField, pollId, index);
      break;
    default:
      focusNextInput(inputField, pollId, index);
      break;
  }

}

function focusPreviousInput(inputField, pollId, index) {
  if (index > 0) {
    document.querySelector("#otp_"+pollId+"__"+(index-1)).focus();
  }
}

function deleteInputAndBack(inputField, pollId, index){
  inputField.value= "";
  focusPreviousInput(inputField, pollId, index);
}

function focusNextInput(inputField, pollId, index) {
  if (index < 4) {
    document.querySelector("#otp_"+pollId+"__"+(index+1)).focus();
  } else {
    htmx.trigger("#form-" + pollId, "submit");
    //inputField.blur();
    //document.querySelector("#submit-"+ pollId).focus();
  }
}

function toggleFantaCISF(memberID) {
  memberCard = document.querySelector('#member_' + memberID + '_card');
  memberCard.classList.toggle('bg-primary');
  memberCard.classList.toggle('text-white');
  memberCard.querySelector(".badge").classList.toggle('bg-primary');
  memberCard.querySelector(".badge").classList.toggle('bg-light');
}