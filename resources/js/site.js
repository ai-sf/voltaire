 // Define Alpine.js methods

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
    inputField.blur();
    document.querySelector("#submit-"+ pollId).focus();
  }
}