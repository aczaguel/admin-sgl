console.log(perMonth);
var local_total = parseInt(graphData.local.verde) + parseInt(graphData.local.amarillo) + parseInt(graphData.local.rojo) + parseInt(graphData.local.violeta);
var foraneo_total = parseInt(graphData.foraneo.verde) + parseInt(graphData.foraneo.amarillo) + parseInt(graphData.foraneo.rojo) + parseInt(graphData.foraneo.violeta);

var porcentaje_local_verde = parseInt((graphData.local.verde / local_total) * 100);
var porcentaje_local_amarillo = parseInt((graphData.local.amarillo / local_total) * 100);
var porcentaje_local_rojo = parseInt((graphData.local.rojo / local_total) * 100);
var porcentaje_local_violeta = parseInt((graphData.local.violeta / local_total) * 100);

var porcentaje_foraneo_verde = parseInt((graphData.foraneo.verde / foraneo_total) * 100);
var porcentaje_foraneo_amarillo = parseInt((graphData.foraneo.amarillo / foraneo_total) * 100);
var porcentaje_foraneo_rojo = parseInt((graphData.foraneo.rojo / foraneo_total) * 100);
var porcentaje_foraneo_violeta = parseInt((graphData.foraneo.violeta / foraneo_total) * 100);

// Mapeo de meses para convertir números de mes a nombres de mes
const meses = [
    'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
    'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
];

// Inicializar arrays para las categorías y los datos de las series
let categories = [];
let enProcesoData = [];
let concluidosData = [];

// Procesar el objeto de datos
for (let key in perMonth) {
    let [anio, mesNumero] = key.split('-');
    let nombreMes = meses[parseInt(mesNumero) - 1] + ' ' + anio; // Convertir a nombre de mes y año
    
    categories.push(nombreMes); // Añadir el nombre del mes a las categorías
    enProcesoData.push(parseInt(perMonth[key].recoleccion)); // Añadir los datos de "En Proceso"
    concluidosData.push(parseInt(perMonth[key].concluidos)); // Añadir los datos de "Concluidos"
}

var options = {
	series: [85],
	grid: {
		padding: {
			top: 0,
			right: 0,
			bottom: 0,
			left: 0
		},
	},
	chart: {
		height: 100,
		width: 70,
		type: 'radialBar',
	},	
	plotOptions: {
		radialBar: {
			hollow: {
				size: '50%',
			},
			dataLabels: {
				name: {
					show: false,
					color: '#fff'
				},
				value: {
					show: true,
					color: '#333',
					offsetY: 5,
					fontSize: '15px'
				}
			}
		}
	},
	colors: ['#ecf0f4'],
	fill: {
		type: 'gradient',
		gradient: {
			shade: 'dark',
			type: 'diagonal1',
			shadeIntensity: 0.8,
			gradientToColors: ['#1b00ff'],
			inverseColors: false,
			opacityFrom: [1, 0.2],
			opacityTo: 1,
			stops: [0, 100],
		}
	},
	states: {
		normal: {
			filter: {
				type: 'none',
				value: 0,
			}
		},
		hover: {
			filter: {
				type: 'none',
				value: 0,
			}
		},
		active: {
			filter: {
				type: 'none',
				value: 0,
			}
		},
	}
};

var options2 = {
	series: [70],
	grid: {
		padding: {
			top: 0,
			right: 0,
			bottom: 0,
			left: 0
		},
	},
	chart: {
		height: 100,
		width: 70,
		type: 'radialBar',
	},	
	plotOptions: {
		radialBar: {
			hollow: {
				size: '50%',
			},
			dataLabels: {
				name: {
					show: false,
					color: '#fff'
				},
				value: {
					show: true,
					color: '#333',
					offsetY: 5,
					fontSize: '15px'
				}
			}
		}
	},
	colors: ['#ecf0f4'],
	fill: {
		type: 'gradient',
		gradient: {
			shade: 'dark',
			type: 'diagonal1',
			shadeIntensity: 1,
			gradientToColors: ['#009688'],
			inverseColors: false,
			opacityFrom: [1, 0.2],
			opacityTo: 1,
			stops: [0, 100],
		}
	},
	states: {
		normal: {
			filter: {
				type: 'none',
				value: 0,
			}
		},
		hover: {
			filter: {
				type: 'none',
				value: 0,
			}
		},
		active: {
			filter: {
				type: 'none',
				value: 0,
			}
		},
	}
};

var options3 = {
	series: [75],
	grid: {
		padding: {
			top: 0,
			right: 0,
			bottom: 0,
			left: 0
		},
	},
	chart: {
		height: 100,
		width: 70,
		type: 'radialBar',
	},	
	plotOptions: {
		radialBar: {
			hollow: {
				size: '50%',
			},
			dataLabels: {
				name: {
					show: false,
					color: '#fff'
				},
				value: {
					show: true,
					color: '#333',
					offsetY: 5,
					fontSize: '15px'
				}
			}
		}
	},
	colors: ['#ecf0f4'],
	fill: {
		type: 'gradient',
		gradient: {
			shade: 'dark',
			type: 'diagonal1',
			shadeIntensity: 0.8,
			gradientToColors: ['#f56767'],
			inverseColors: false,
			opacityFrom: [1, 0.2],
			opacityTo: 1,
			stops: [0, 100],
		}
	},
	states: {
		normal: {
			filter: {
				type: 'none',
				value: 0,
			}
		},
		hover: {
			filter: {
				type: 'none',
				value: 0,
			}
		},
		active: {
			filter: {
				type: 'none',
				value: 0,
			}
		},
	}
};

var options4 = {
	series: [85],
	grid: {
		padding: {
			top: 0,
			right: 0,
			bottom: 0,
			left: 0
		},
	},
	chart: {
		height: 100,
		width: 70,
		type: 'radialBar',
	},	
	plotOptions: {
		radialBar: {
			hollow: {
				size: '50%',
			},
			dataLabels: {
				name: {
					show: false,
					color: '#fff'
				},
				value: {
					show: true,
					color: '#333',
					offsetY: 5,
					fontSize: '15px'
				}
			}
		}
	},
	colors: ['#ecf0f4'],
	fill: {
		type: 'gradient',
		gradient: {
			shade: 'dark',
			type: 'diagonal1',
			shadeIntensity: 0.8,
			gradientToColors: ['#2979ff'],
			inverseColors: false,
			opacityFrom: [1, 0.5],
			opacityTo: 1,
			stops: [0, 100],
		}
	},
	states: {
		normal: {
			filter: {
				type: 'none',
				value: 0,
			}
		},
		hover: {
			filter: {
				type: 'none',
				value: 0,
			}
		},
		active: {
			filter: {
				type: 'none',
				value: 0,
			}
		},
	}
};

// var options5 = {
// 	chart: {
// 		height: 350,
// 		type: 'bar',
// 		parentHeightOffset: 0,
// 		fontFamily: 'Poppins, sans-serif',
// 		toolbar: {
// 			show: false,
// 		},
// 	},
// 	colors: ['#1b00ff', '#f56767'],
// 	grid: {
// 		borderColor: '#c7d2dd',
// 		strokeDashArray: 5,
// 	},
// 	plotOptions: {
// 		bar: {
// 			horizontal: false,
// 			columnWidth: '25%',
// 			endingShape: 'rounded'
// 		},
// 	},
// 	dataLabels: {
// 		enabled: false
// 	},
// 	stroke: {
// 		show: true,
// 		width: 2,
// 		colors: ['transparent']
// 	},
// 	series: [{
// 		name: 'In Progress',
// 		data: [40, 28, 47, 22, 34, 25]
// 	}, {
// 		name: 'Complete',
// 		data: [30, 20, 37, 10, 28, 11]
// 	}],
// 	xaxis: {
// 		categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
// 		labels: {
// 			style: {
// 				colors: ['#353535'],
// 				fontSize: '16px',
// 			},
// 		},
// 		axisBorder: {
// 			color: '#8fa6bc',
// 		}
// 	},
// 	yaxis: {
// 		title: {
// 			text: ''
// 		},
// 		labels: {
// 			style: {
// 				colors: '#353535',
// 				fontSize: '16px',
// 			},
// 		},
// 		axisBorder: {
// 			color: '#f00',
// 		}
// 	},
// 	legend: {
// 		horizontalAlign: 'right',
// 		position: 'top',
// 		fontSize: '16px',
// 		offsetY: 0,
// 		labels: {
// 			colors: '#353535',
// 		},
// 		markers: {
// 			width: 10,
// 			height: 10,
// 			radius: 15,
// 		},
// 		itemMargin: {
// 			vertical: 0
// 		},
// 	},
// 	fill: {
// 		opacity: 1

// 	},
// 	tooltip: {
// 		style: {
// 			fontSize: '15px',
// 			fontFamily: 'Poppins, sans-serif',
// 		},
// 		y: {
// 			formatter: function (val) {
// 				return val
// 			}
// 		}
// 	}
// }

// Configuración del gráfico con las nuevas categorías y series de datos
var options5 = {
    chart: {
        height: 350,
        type: 'bar',
        parentHeightOffset: 0,
        fontFamily: 'Poppins, sans-serif',
        toolbar: {
            show: false,
        },
    },
    colors: ['#f56767', '#1b00ff'],
    grid: {
        borderColor: '#c7d2dd',
        strokeDashArray: 5,
    },
    plotOptions: {
        bar: {
            horizontal: false,
            columnWidth: '25%',
            endingShape: 'rounded'
        },
    },
    dataLabels: {
        enabled: false
    },
    stroke: {
        show: true,
        width: 2,
        colors: ['transparent']
    },
    series: [{
        name: 'En Proceso',
        data: enProcesoData // Datos para "En Proceso"
    }, {
        name: 'Concluidos',
        data: concluidosData // Datos para "Concluidos"
    }],
    xaxis: {
        categories: categories, // Categorías con nombres de meses
        labels: {
            style: {
                colors: ['#353535'],
                fontSize: '16px',
            },
        },
        axisBorder: {
            color: '#8fa6bc',
        }
    },
    yaxis: {
        title: {
            text: ''
        },
        labels: {
            style: {
                colors: '#353535',
                fontSize: '16px',
            },
        },
        axisBorder: {
            color: '#f00',
        }
    },
    legend: {
        horizontalAlign: 'right',
        position: 'top',
        fontSize: '16px',
        offsetY: 0,
        labels: {
            colors: '#353535',
        },
        markers: {
            width: 10,
            height: 10,
            radius: 15,
        },
        itemMargin: {
            vertical: 0
        },
    },
    fill: {
        opacity: 1
    },
    tooltip: {
        style: {
            fontSize: '15px',
            fontFamily: 'Poppins, sans-serif',
        },
        y: {
            formatter: function (val) {
                return val;
            }
        }
    }
};


var options6 = {
	series: [73],
	chart: {
	  height: 350,
	  type: 'radialBar',
	  offsetY: 0
	},
	colors: ['#0B132B', '#222222'],
	plotOptions: {
	  radialBar: {
		startAngle: -135,
		endAngle: 135,
		dataLabels: {
		  name: {
			fontSize: '16px',
			color: undefined,
			offsetY: 120
		  },
		  value: {
			offsetY: 76,
			fontSize: '22px',
			color: undefined,
			formatter: function (val) {
			  return val + "%";
			}
		  }
		}
	  }
	},
	fill: {
	  type: 'gradient',
	  gradient: {
		  shade: 'dark',
		  shadeIntensity: 0.15,
		  inverseColors: false,
		  opacityFrom: 1,
		  opacityTo: 1,
		  stops: [0, 50, 65, 91]
	  },
	},
	stroke: {
	  dashArray: 4
	},
	labels: ['Achieve Goals'],
};

var options_local_verde = {
	series: [porcentaje_local_verde],
	grid: {
		padding: {
			top: 0,
			right: 0,
			bottom: 0,
			left: 0
		},
	},
	chart: {
		height: 100,
		width: 70,
		type: 'radialBar',
	},	
	plotOptions: {
		radialBar: {
			hollow: {
				size: '50%',
			},
			dataLabels: {
				name: {
					show: false,
					color: '#fff'
				},
				value: {
					show: true,
					color: '#333',
					offsetY: 5,
					fontSize: '15px'
				}
			}
		}
	},
	colors: ['#ecf0f4'],
	fill: {
		type: 'gradient',
		gradient: {
			shade: 'dark',
			type: 'diagonal1',
			shadeIntensity: 0.8,
			gradientToColors: ['#008000'],
			inverseColors: false,
			opacityFrom: [1, 0.2],
			opacityTo: 1,
			stops: [0, 100],
		}
	},
	states: {
		normal: {
			filter: {
				type: 'none',
				value: 0,
			}
		},
		hover: {
			filter: {
				type: 'none',
				value: 0,
			}
		},
		active: {
			filter: {
				type: 'none',
				value: 0,
			}
		},
	}
};

var options_local_amarillo = {
	series: [porcentaje_local_amarillo],
	grid: {
		padding: {
			top: 0,
			right: 0,
			bottom: 0,
			left: 0
		},
	},
	chart: {
		height: 100,
		width: 70,
		type: 'radialBar',
	},	
	plotOptions: {
		radialBar: {
			hollow: {
				size: '50%',
			},
			dataLabels: {
				name: {
					show: false,
					color: '#fff'
				},
				value: {
					show: true,
					color: '#333',
					offsetY: 5,
					fontSize: '15px'
				}
			}
		}
	},
	colors: ['#ecf0f4'],
	fill: {
		type: 'gradient',
		gradient: {
			shade: 'dark',
			type: 'diagonal1',
			shadeIntensity: 0.8,
			gradientToColors: ['#FFFF00'],
			inverseColors: false,
			opacityFrom: [1, 0.2],
			opacityTo: 1,
			stops: [0, 100],
		}
	},
	states: {
		normal: {
			filter: {
				type: 'none',
				value: 0,
			}
		},
		hover: {
			filter: {
				type: 'none',
				value: 0,
			}
		},
		active: {
			filter: {
				type: 'none',
				value: 0,
			}
		},
	}
};

var options_local_rojo = {
	series: [porcentaje_local_rojo],
	grid: {
		padding: {
			top: 0,
			right: 0,
			bottom: 0,
			left: 0
		},
	},
	chart: {
		height: 100,
		width: 70,
		type: 'radialBar',
	},	
	plotOptions: {
		radialBar: {
			hollow: {
				size: '50%',
			},
			dataLabels: {
				name: {
					show: false,
					color: '#fff'
				},
				value: {
					show: true,
					color: '#333',
					offsetY: 5,
					fontSize: '15px'
				}
			}
		}
	},
	colors: ['#ecf0f4'],
	fill: {
		type: 'gradient',
		gradient: {
			shade: 'dark',
			type: 'diagonal1',
			shadeIntensity: 0.8,
			gradientToColors: ['#FF0000'],
			inverseColors: false,
			opacityFrom: [1, 0.2],
			opacityTo: 1,
			stops: [0, 100],
		}
	},
	states: {
		normal: {
			filter: {
				type: 'none',
				value: 0,
			}
		},
		hover: {
			filter: {
				type: 'none',
				value: 0,
			}
		},
		active: {
			filter: {
				type: 'none',
				value: 0,
			}
		},
	}
};


var options_local_violeta = {
	series: [porcentaje_local_violeta],
	grid: {
		padding: {
			top: 0,
			right: 0,
			bottom: 0,
			left: 0
		},
	},
	chart: {
		height: 100,
		width: 70,
		type: 'radialBar',
	},	
	plotOptions: {
		radialBar: {
			hollow: {
				size: '50%',
			},
			dataLabels: {
				name: {
					show: false,
					color: '#fff'
				},
				value: {
					show: true,
					color: '#333',
					offsetY: 5,
					fontSize: '15px'
				}
			}
		}
	},
	colors: ['#ecf0f4'],
	fill: {
		type: 'gradient',
		gradient: {
			shade: 'dark',
			type: 'diagonal1',
			shadeIntensity: 0.8,
			gradientToColors: ['#EE82EE'],
			inverseColors: false,
			opacityFrom: [1, 0.2],
			opacityTo: 1,
			stops: [0, 100],
		}
	},
	states: {
		normal: {
			filter: {
				type: 'none',
				value: 0,
			}
		},
		hover: {
			filter: {
				type: 'none',
				value: 0,
			}
		},
		active: {
			filter: {
				type: 'none',
				value: 0,
			}
		},
	}
};


var options_foraneo_verde = {
	series: [porcentaje_foraneo_verde],
	grid: {
		padding: {
			top: 0,
			right: 0,
			bottom: 0,
			left: 0
		},
	},
	chart: {
		height: 100,
		width: 70,
		type: 'radialBar',
	},	
	plotOptions: {
		radialBar: {
			hollow: {
				size: '50%',
			},
			dataLabels: {
				name: {
					show: false,
					color: '#fff'
				},
				value: {
					show: true,
					color: '#333',
					offsetY: 5,
					fontSize: '15px'
				}
			}
		}
	},
	colors: ['#ecf0f4'],
	fill: {
		type: 'gradient',
		gradient: {
			shade: 'dark',
			type: 'diagonal1',
			shadeIntensity: 0.8,
			gradientToColors: ['#008000'],
			inverseColors: false,
			opacityFrom: [1, 0.2],
			opacityTo: 1,
			stops: [0, 100],
		}
	},
	states: {
		normal: {
			filter: {
				type: 'none',
				value: 0,
			}
		},
		hover: {
			filter: {
				type: 'none',
				value: 0,
			}
		},
		active: {
			filter: {
				type: 'none',
				value: 0,
			}
		},
	}
};

var options_foraneo_amarillo = {
	series: [porcentaje_foraneo_amarillo],
	grid: {
		padding: {
			top: 0,
			right: 0,
			bottom: 0,
			left: 0
		},
	},
	chart: {
		height: 100,
		width: 70,
		type: 'radialBar',
	},	
	plotOptions: {
		radialBar: {
			hollow: {
				size: '50%',
			},
			dataLabels: {
				name: {
					show: false,
					color: '#fff'
				},
				value: {
					show: true,
					color: '#333',
					offsetY: 5,
					fontSize: '15px'
				}
			}
		}
	},
	colors: ['#ecf0f4'],
	fill: {
		type: 'gradient',
		gradient: {
			shade: 'dark',
			type: 'diagonal1',
			shadeIntensity: 0.8,
			gradientToColors: ['#FFFF00'],
			inverseColors: false,
			opacityFrom: [1, 0.2],
			opacityTo: 1,
			stops: [0, 100],
		}
	},
	states: {
		normal: {
			filter: {
				type: 'none',
				value: 0,
			}
		},
		hover: {
			filter: {
				type: 'none',
				value: 0,
			}
		},
		active: {
			filter: {
				type: 'none',
				value: 0,
			}
		},
	}
};

var options_foraneo_rojo = {
	series: [porcentaje_foraneo_rojo],
	grid: {
		padding: {
			top: 0,
			right: 0,
			bottom: 0,
			left: 0
		},
	},
	chart: {
		height: 100,
		width: 70,
		type: 'radialBar',
	},	
	plotOptions: {
		radialBar: {
			hollow: {
				size: '50%',
			},
			dataLabels: {
				name: {
					show: false,
					color: '#fff'
				},
				value: {
					show: true,
					color: '#333',
					offsetY: 5,
					fontSize: '15px'
				}
			}
		}
	},
	colors: ['#ecf0f4'],
	fill: {
		type: 'gradient',
		gradient: {
			shade: 'dark',
			type: 'diagonal1',
			shadeIntensity: 0.8,
			gradientToColors: ['#FF0000'],
			inverseColors: false,
			opacityFrom: [1, 0.2],
			opacityTo: 1,
			stops: [0, 100],
		}
	},
	states: {
		normal: {
			filter: {
				type: 'none',
				value: 0,
			}
		},
		hover: {
			filter: {
				type: 'none',
				value: 0,
			}
		},
		active: {
			filter: {
				type: 'none',
				value: 0,
			}
		},
	}
};


var options_foraneo_violeta = {
	series: [porcentaje_foraneo_violeta],
	grid: {
		padding: {
			top: 0,
			right: 0,
			bottom: 0,
			left: 0
		},
	},
	chart: {
		height: 100,
		width: 70,
		type: 'radialBar',
	},	
	plotOptions: {
		radialBar: {
			hollow: {
				size: '50%',
			},
			dataLabels: {
				name: {
					show: false,
					color: '#fff'
				},
				value: {
					show: true,
					color: '#333',
					offsetY: 5,
					fontSize: '15px'
				}
			}
		}
	},
	colors: ['#ecf0f4'],
	fill: {
		type: 'gradient',
		gradient: {
			shade: 'dark',
			type: 'diagonal1',
			shadeIntensity: 0.8,
			gradientToColors: ['#EE82EE'],
			inverseColors: false,
			opacityFrom: [1, 0.2],
			opacityTo: 1,
			stops: [0, 100],
		}
	},
	states: {
		normal: {
			filter: {
				type: 'none',
				value: 0,
			}
		},
		hover: {
			filter: {
				type: 'none',
				value: 0,
			}
		},
		active: {
			filter: {
				type: 'none',
				value: 0,
			}
		},
	}
};



var chart = new ApexCharts(document.querySelector("#chart"), options_local_verde);
chart.render();

var chart2 = new ApexCharts(document.querySelector("#chart2"), options_local_amarillo);
chart2.render();

var chart3 = new ApexCharts(document.querySelector("#chart3"), options_local_rojo);
chart3.render();

var chart4 = new ApexCharts(document.querySelector("#chart4"), options_local_violeta);
chart4.render();

var chartf1 = new ApexCharts(document.querySelector("#chartf1"), options_foraneo_verde);
chartf1.render();

var chartf2 = new ApexCharts(document.querySelector("#chartf2"), options_foraneo_amarillo);
chartf2.render();

var chartf3 = new ApexCharts(document.querySelector("#chartf3"), options_foraneo_rojo);
chartf3.render();

var chartf4 = new ApexCharts(document.querySelector("#chartf4"), options_foraneo_violeta);
chartf4.render();



var chart5 = new ApexCharts(document.querySelector("#chart5"), options5);
chart5.render();

var chart6 = new ApexCharts(document.querySelector("#chart6"), options6);
chart6.render();


// datatable init
$('document').ready(function(){
	$('.data-table').DataTable({
		scrollCollapse: true,
		autoWidth: true,
		responsive: true,
		searching: false,
		bLengthChange: false,
		bPaginate: false,
		bInfo: false,
		columnDefs: [{
			targets: "datatable-nosort",
			orderable: false,
		}],
		"lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
		"language": {
			"info": "_START_-_END_ of _TOTAL_ entries",
			searchPlaceholder: "Search",
			paginate: {
				next: '<i class="ion-chevron-right"></i>',
				previous: '<i class="ion-chevron-left"></i>'  
			}
		},
	});
});