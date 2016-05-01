
$(document).ready(function() {

	// Append the option elements
	/*for ( var i = 0; i <= 100; i++ ){

		var option = document.createElement("option");
			option.text = i;
			option.value = i;

		select.appendChild(option);
	}*/
	
	var html5Slider = document.getElementById('distancia');
	var inputNumber = document.getElementById('input-number1');
	var inputNumber2 = document.getElementById('input-number2');

	noUiSlider.create(html5Slider, {
		start: [ 0, 20 ],
		connect: true,
		range: {
			'min': 0,
			'max': 100
		}
	});

	html5Slider.noUiSlider.on('update', function( values, handle ) {

		var value = values[handle];

		if ( handle ) {
			inputNumber.value = value;
		} else {
			inputNumber2.value = Math.round(value);
		}
	});

	inputNumber2.addEventListener('change', function(){
		html5Slider.noUiSlider.set([this.value, null]);
	});

	inputNumber.addEventListener('change', function(){
		html5Slider.noUiSlider.set([null, this.value]);
	});
	
	var precioSlider = document.getElementById('precio');
	var inputNumber3 = document.getElementById('input-number3');
	var inputNumber4 = document.getElementById('input-number4');

	noUiSlider.create(precioSlider, {
		start: [ 0, 20 ],
		connect: true,
		range: {
			'min': 0,
			'max': 100
		}
	});

	precioSlider.noUiSlider.on('update', function( values, handle ) {

		var valor = values[handle];

		if ( handle ) {
			inputNumber3.value = valor;
		} else {
			inputNumber4.value = Math.round(valor);
		}
	});

	inputNumber3.addEventListener('change', function(){
		precioSlider.noUiSlider.set([this.valor, null]);
	});

	inputNumber4.addEventListener('change', function(){
		precioSlider.noUiSlider.set([null, this.valor]);
	});
	
});