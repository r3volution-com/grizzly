$(document).ready(function() {
	var precioSlider2 = document.getElementById('precio2');
	var inputNumber5 = document.getElementById('input-number5');
	var inputNumber6 = document.getElementById('input-number6');

	noUiSlider.create(precioSlider2, {
		start: [ 0, 20 ],
		connect: true,
		range: {
			'min': 0,
			'max': 100
		}
	});

	precioSlider2.noUiSlider.on('update', function( values, handle ) {

		var valor2 = values[handle];

		if ( handle ) {
			inputNumber5.value = valor2;
		} else {
			inputNumber6.value = Math.round(valor2);
		}
	});

	inputNumber5.addEventListener('change', function(){
		precioSlider2.noUiSlider.set([this.valor2, null]);
	});

	inputNumber6.addEventListener('change', function(){
		precioSlider2.noUiSlider.set([null, this.valor2]);
});
})