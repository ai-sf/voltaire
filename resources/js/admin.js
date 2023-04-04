function randomToken() {
  // Generate a random array of bytes
  const array = new Uint8Array(5);
  crypto.getRandomValues(array);

  // Convert the array to a number
  let number = 0;
  for (let i = 0; i < array.length; i++) {
    number = number * 256 + array[i];
  }

  // Make sure the number has exactly 5 digits
  number = number % 100000;

  // Pad the number with leading zeros if necessary
  return number.toString().padStart(5, '0');
}