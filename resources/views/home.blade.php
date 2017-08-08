@extends('layouts.app')

@section('content')

<style>

.green {
    color: green;
}

.gray {
    color: gray;
}
</style>

<div class="container">
    <div class="row">
        <div>
            <div class="panel panel-default">
                <div class="panel-heading">Dashboard</div>

                <div class="panel-body">
                    <div class="row">
                        <ul class="nav nav-tabs">
                             <li class="active"><a data-toggle="tab" href="#general">CCMS</a></li>
                             <li><a data-toggle="tab" href="#nodos">Nodos</a></li>
                             <li><a data-toggle="tab" href="#aparatos">Aparatos</a></li>
                        </ul>
                        <div class="tab-content">
                            <div id="general" class="tab-pane fade in active">
                                <div class="col-md-4" style="padding-top:20px;">
                                    <p>
                                    <input
                                        id="trigger"
                                        type="text"
                                        name="somename"
                                        data-provide="slider"
                                        data-slider-min="-100"
                                        data-slider-max="0"
                                        data-slider-step="1"
                                        data-slider-value="{{$trigger}}"
                                    >
                                    <span id="trigger_show"></span>
                                    <button class="btn btn-success" id="change_trigger">Aplicar</button>
                                    </p>
                                    <p><b>Total Aparatos:</b> {{$total_aparatos}}</p>
                                    <p><b>Aparatos Graficados:</b> {{$aparatos_consultados}}</p>
                                    <p><b>Fecha Poll:</b> <span id="fecha_poll"></span></p>
                                    </ol>
                                </div>                                            
                                <div class="col-md-8">
                                    <div id="ccms" "width: 650px; height: 400px; margin: 0 auto"></div>
                                </div>
             
                            </div>
                            <div id="nodos" class="tab-pane fade">
                                    <div id="top_ten_ms" style="min-width: 310px; height: 400px; margin: 0 auto"> </div>
                            </div>
                            <div id="aparatos" class="tab-pane fade">
                                <div class="col-md-4" style="padding-top:20px;">
                                    <div id="tree"></div>
                                </div>
                                 <div class="col-md-8">
                                    <div id="container1" "width: 650px; height: 400px; margin: 0 auto"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>

$(document).ready(function(){

    if (!Notification) {
        alert("Las notificaciones de escritorio no son soportadas por este browser");
    } else {
      if (Notification.permission !== "granted")
          Notification.requestPermission();          
    }

    $('#trigger_show').html($('#trigger').val()+' dB');

    $('#trigger').change(function(){
        $('#trigger_show').html($(this).val()+' dB');
    });

    $('#change_trigger').click(function() {
        $.ajax({
            url: '/generatestats/',
            type: "POST",
            dataType: "json",
            contentType: "application/json",
            data:{"trigger":$('#trigger').val()},
            success: function(result) {
            }
        });

    });
    $('#fecha_poll').html(new Date({{$fecha_poll}}).toLocaleString());
     $('#ccms').highcharts({
         chart: {
                 zoomType: 'x'
         },
            title: {
                text: 'CCMS',
                x: -20 //center
            },
            xAxis: {
                type: 'datetime'
            },
            yAxis: {
                title: {
                text: '#'
                },
                plotLines: [{
                    value: 0,
                    width: 1,
                    color: '#808080'
                }]
            },
            legend: {
                layout: 'vertical',
                align: 'right',
                verticalAlign: 'middle',
                borderWidth: 0
            },
            series: [{
                name: 'CCMS',
                data: {{$ccms_graph}},
                color: '#80ff80'
    }
         ],
    });

    $('#top_ten_ms').highcharts({
    chart: {
    type: 'column',
        width:990,
    },
    title: {
        text: ''
        //text: 'Browser market shares. January, 2015 to May, 2015'
    },
    subtitle: {
        text: ''
        //text: 'Click the columns to view versions. Source: <a href="http://netmarketshare.com">netmarketshare.com</a>.'
    },
    xAxis: {
        type: 'category'
    },
    yAxis: {
        title: {
            text: 'CCMS'
        }

    },
    legend: {
        enabled: false
    },
    plotOptions: {
        series: {
            borderWidth: 0,
            dataLabels: {
                enabled: true,
            }
        }
    },

    tooltip: {
        headerFormat: '<span style="font-size:11px">{series.name}</span><br>',
        pointFormat: '<span style="color:{point.color}">{point.name}</span>: <b>{point.y:.2f}</b> CCMS<br/>'
    },

    series: [{
        name: 'Nodos',
        colorByPoint: true,
        data: [
        @foreach ($nodos_graph as $nodo)
        {
            name: '{{$nodo['nombre']}}',
            y: {{$nodo['score']}},
            drilldown: 'nodo_{{$nodo['id']}}'
        },
        @endforeach
        ]
        }],
    drilldown: {
    series: [
        @foreach ($nodos_graph as $nodo)
        {
        name: '{{$nodo['nombre']}}',
        id: 'nodo_{{$nodo['id']}}',
        data: [
            @foreach($nodo['aparatos'] as $ap)
            ['{{$ap[0]}}',{{$ap[1]}}],
            @endforeach
        ]
        },
        @endforeach
        ]}
    });

});
var aparato_seleccionado=0;

function graficar(aparato_id) {
    aparato_seleccionado=aparato_id;

    $.ajax({
        url: '/stats/'+aparato_id,
        type: "GET",
        dataType: "json",
        contentType: "application/json",
        success: function(result) {

                $('#container1').highcharts({
                 chart: {
                         zoomType: 'x'
                 },
                    title: {
                        text: 'CCMS',
                        x: -20 //center
                    },
                    xAxis: {
                        type: 'datetime'
                    },
                    yAxis: {
                        title: {
                        text: '#'
                        },
                        plotLines: [{
                            value: 0,
                            width: 1,
                            color: '#808080'
                        }]
                    },
                    legend: {
                        layout: 'vertical',
                        align: 'right',
                        verticalAlign: 'middle',
                        borderWidth: 0
                    },
                    series: [{
                        name: 'Signal',
                        data: result.signal,
                        color: '#80ff80'
            }
            ]
            });
        }
});
}

function getTree() {
    return [
    @for ($i = 0; $i < count($nodos); $i++)
        {
        state: {expanded:false},
        text: "{{$nodos[$i]->nombre}}",
            nodes: [
        @foreach($nodos[$i]->aparatos as $aparato)
            {
                text: "<p @if ($aparato->hasStats()) class='green'@else class='gray'@endif>{{$aparato->nombre}}</p>",
                href: "javascript:graficar({{$aparato->id}})"
            },
        @endforeach
        ]},
    @endfor
    ];    
}
$(document).ready(function(){
    $('#tree').treeview({data: getTree(),enableLinks:true});

    $('#tree').on('nodeSelected', function(event, data) {
        console.log(data);
    });
	// configurar Highcharts para que muestre hora local en las gráficas, en lugar de UTC
	Highcharts.setOptions({
		global: {
			useUTC: false
		}
	});
    $('#signal').change(function() {
        graficar(aparato_seleccionado);
    });

    $('#uptime').change(function() {
        graficar(aparato_seleccionado);
    });

    $('#noise').change(function() {
        graficar(aparato_seleccionado);
    });

    $('#cpu').change(function() {
        graficar(aparato_seleccionado);
    });

});
</script>
@endsection

