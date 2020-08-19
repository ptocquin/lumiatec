// any CSS you require will output into a single css file (app.css in this case)
require('../css/app.scss');

const $ = require('jquery');
global.$ = global.jQuery = $;
// var $ = require('jquery');

require('bootstrap');
require('datatables.net-bs4');
require('datatables.net-buttons-bs4');

require('./jquery.collection.js');

require('@fortawesome/fontawesome-free/css/regular.min.css');
require('@fortawesome/fontawesome-free/js/regular.js');

require('chart.js')

// https://stackoverflow.com/questions/48763659/no-service-fosjsroutingbundle-in-symfony-4
const routes = require('../../public/js/fos_js_routes.json');
import Routing from '../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js';
Routing.setRoutingData(routes);

console.log(Routing.generate('set-cluster'));


jQuery(document).ready(function () {
	console.log('1. Hello Webpack Encore! Edit me in assets/js/app.js');


	$('.table').DataTable();

	$('.toast').toast('show');

	$('.alert').fadeOut(5000);

	$('.form-collection').collection({
			position_field_selector: '.rank',
			allow_duplicate: true,
			allow_up: true,
	 		allow_down: true,
	 	}
  	);

	$('.cluster-plus').on('click', function(){
		var label = $(this).next();
		var input = $('input[name=cluster]', this);
		var cluster = parseInt($('input[name=cluster]', this).val())+1;
		var luminaire = $('input[name=luminaire]', this).val();
		var controller = $('input[name=controller]', this).val();
		var d = {l: luminaire, c: cluster, ctrl: controller}
		$.ajax({
			type: 'post',
			url: Routing.generate('set-cluster'),
			data: {'data': d },
			beforeSend: function() {
				console.log('chargement !')
				console.log(d);
			},
			success: function(response) {
				console.log(response);
 				label.text(response.c);
 				$('.cluster-label').val(response.c);
 				if (response.cluster_added == 1) {
 					location.reload();
 				}
			}
		})

		console.log(cluster,luminaire);
	})

	$('.cluster-minus').on('click', function(){
		var label = $(this).prev();
		var input = $('input[name=cluster]', this);
		if (parseInt($('input[name=cluster]', this).val()) == 1) {
			var cluster = 1;
		} else {
			var cluster = parseInt($('input[name=cluster]', this).val())-1;
		}
		var luminaire = $('input[name=luminaire]', this).val();
		var controller = $('input[name=controller]', this).val();
		var d = {l: luminaire, c: cluster, ctrl: controller}
		$.ajax({
			type: 'post',
			url: Routing.generate('set-cluster'),
			data: {'data': d },
			beforeSend: function() {
				console.log('chargement !')
				console.log(d);
			},
			success: function(response) {
				console.log(response);
 				label.text(response.c);
 				$('.cluster-label').val(response.c);
 				if (response.cluster_added == 1) {
 					location.reload();
 				}
 				
			}
		})

		console.log(cluster,luminaire);
	})

	$('.set-position').on('click', function(){
		var id = $("input[name=set_position]", this).val();
		var controller = $("input[name=controller_id]", this).val();
		var x_pos = $("#"+id+"_colonne").val();
		var y_pos = $("#"+id+"_ligne").val();
		var positions = {id: id, x: x_pos, y: y_pos, ctrl: controller};
  		$.ajax({
			type: 'post',
			url: Routing.generate('set-position'),
			data: {'data': positions },
			beforeSend: function() {
				console.log('chargement !')
				console.log(positions);
			},
			success: function(response) {
				console.log(response);
 				location.reload();
			}
		})
	})

	$('.clk_increment').on('click', function() {
		console.log("click");
		var old = parseInt($("input", this).val());
		if(old < 10) {
			var next =  old + 1;
		} else {
			var next = 1;
		}
		console.log(old+" > "+next);
		$("input", this).val(next);
		$(".value", this).html(next);
	});

});
