<?php
ini_set("memory_limit" , -1);
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>

<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>부동산 차트</title>
<script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
</head>
<style>
html,
body {
  height: 100%;
}

body {
  background-color: #f5f5f5;
}

.chart {
    text-align: center;
}
.chart > div {
    display: inline-block;
}
</style>

<body>
    <div class="container-fluid">
        <div class="row m-0 mt-2 mb-2">
            <ul class="list-group list-group-horizontal">
                <a href="#" class="list-menu" data-menu="simri"><li class="list-group-item" title="매주 금요일 업데이트(KB주간)">1. 심리차트(광역시,도)</li></a>
                <a href="#" class="list-menu" data-menu="m1m2"><li class="list-group-item">2. M1/M2차트</li></a>
                <a href="#" class="list-menu" data-menu="jeonmang"><li class="list-group-item" title="매달 말일 업데이트(KB월간)">3. 전망차트(광역시,도)</li></a>
                <a href="#" class="list-menu" data-menu="jeonmang2"><li class="list-group-item" title="매주 금요일 업데이트(KB주간)">3-1. 전망차트(시군구)</li></a>
                <a href="#" class="list-menu" data-menu="choongjeon"><li class="list-group-item" title="매주 금요일 업데이트(KB주간)">4. 충전차트(시군구)</li></a>
                <a href="#" class="list-menu" data-menu="miboonyang"><li class="list-group-item">5. 미분양차트</li></a>
                <a href="#" class="list-menu" data-menu="ingoo"><li class="list-group-item">6. 인구수차트</li></a>
                <a href="#" class="list-menu" data-menu="ingooidong"><li class="list-group-item">7. 인구이동차트</li></a>
                <a href="#" class="list-menu" data-menu="age"><li class="list-group-item">8. 평균연령차트</li></a>
                <a href="#" class="list-menu" data-menu="apt"><li class="list-group-item">9. 아파트</li></a>
                <a href="#" class="list-menu" data-menu="azzi"><li class="list-group-item">10. 아찌</li></a>
            </ul>
        </div>

        <div class="row m-0 mt-2 mb-2">
            <div class="wrap-chart"></div>
        </div>
    </div>
</body>

<script>
var menu = null;
var chartType = 'LineChart';
var chartDetail = {};
var region = '';
var rankData = null;

function clickMenu(e) {
    menu = $(e.currentTarget).data('menu');

    if (!menu) return false;

    $.ajax({
        type: "POST",
        url: '../api/yoona.php',
        dataType : 'json',
        cache: false,
        timeout: 100000,
        data: {
            menu: menu
        },
        success: function (result, textStatus) {
            console.log(result);
            console.log(textStatus);
            clearChart();
            window['render' + menu](result);
        },
        error: function(result, textStatus, jqXHR) {
            console.log(result);
            console.log(textStatus);
            console.log(jqXHR);
        },
        complete: function() {
        }
    });

    return false;
}

function clickSigoongoo(e) {
    extra = $(e.currentTarget).data('code');

    if (!extra) return false;

    $.ajax({
        type: "POST",
        url: '../api/yoona.php',
        dataType : 'json',
        cache: false,
        timeout: 100000,
        data: {
            menu: menu + '_detail',
            extra: extra
        },
        success: function (result, textStatus) {
            console.log(result);
            console.log(textStatus);
            window['render' + menu + 'Detail'](result);
        },
        error: function(result, textStatus, jqXHR) {
            console.log(result);
            console.log(textStatus);
            console.log(jqXHR);
        },
        complete: function() {
        }
    });

    return false;
}

function callApi(data, callback) {
    if (!data) return false;

    $.ajax({
        type: "POST",
        url: '../api/yoona.php',
        dataType : 'json',
        cache: false,
        timeout: 100000,
        data: data,
        success: function (result, textStatus) {
            console.log(result);
            console.log(textStatus);
            if (callback && typeof callback == 'function') {
                callback(result);
            }
        },
        error: function(result, textStatus, jqXHR) {
            console.log(result);
            console.log(textStatus);
            console.log(jqXHR);
        },
        complete: function() {
        }
    });

    return false;
}

// 1. 심리차트 + 전세수급
function rendersimri(data) {
    // 데이터 준비
    if (data) {
        var chartData = {};
        var chartDataJeonse = {};
        var chartDataMaeJeon = {};
        var chartTitle = {};
        var chartTitleJeonse = {};
        var chartTitleMaeJeon = {};
        var year = null;
        var lastDate = null;

        // 심리차트
        for (var i = 0; i < data['simri'].length; i++) {
            if (i == 0) continue;
            if (i == 2) continue;
            if (i == 3) continue;

            if (i == 1) {
                for (var j = 0; j < data['simri'][i].length; j++) {
                    if (data['simri'][i][j]) {
                        // 오래 걸려서 특정 지역만 보기
                        if (region && data['simri'][i][j].indexOf(region) == -1) continue;

                        chartTitle[j] = data['simri'][i][j];
                    }
                }

                continue;
            }

            if (!data['simri'][i][0]) continue;

            // 연도 생성
            var date = data['simri'][i][0].split('/');

            if (date.length == 3) {
                year = date[0];
            } else {
                data['simri'][i][0] = year + '/' + data['simri'][i][0];
            }

            // 이 코드는 대체 뭐지???
            // if (year < 8) continue;
            // else if (year == 8 && date[0] < 4) continue;

            for (key in chartTitle) {
                if (!chartData[key]) {
                    chartData[key] = [];
                }

                chartData[key].push([data['simri'][i][0], parseFloat(data['simri'][i][key]), parseFloat(data['simri'][i][(parseInt(key) + 1)])]);
            }

            lastDate = data['simri'][i][0];
        }

        // 전세수급차트
        for (var i = 0; i < data['jeonse'].length; i++) {
            if (i == 0) continue;
            if (i == 2) continue;
            if (i == 3) continue;

            if (i == 1) {
                for (var j = 0; j < data['jeonse'][i].length; j++) {
                    if (data['jeonse'][i][j]) {
                        // 오래 걸려서 특정 지역만 보기
                        if (region && data['jeonse'][i][j].indexOf(region) == -1) continue;

                        chartTitleJeonse[j] = data['jeonse'][i][j];
                    }
                }

                continue;
            }

            if (!data['jeonse'][i][0]) continue;

            // 연도 생성
            var date = data['jeonse'][i][0].split('/');

            if (date.length == 3) {
                year = date[0];
            } else {
                data['jeonse'][i][0] = year + '/' + data['jeonse'][i][0];
            }

            // if (year < 8) continue;
            // else if (year == 8 && date[0] < 4) continue;

            for (key in chartTitleJeonse) {
                if (!chartDataJeonse[key]) {
                    chartDataJeonse[key] = [];
                }

                chartDataJeonse[key].push([data['jeonse'][i][0], parseFloat(data['jeonse'][i][key]), parseFloat(data['jeonse'][i][(parseInt(key) + 1)]), parseFloat(data['jeonse'][i][(parseInt(key) + 2)])]);
            }
        }

        // 매매, 전세 증감
        // for (var i = 0; i < data['maemae1'].length; i++) {
        //     if (i == 0) continue;
        //     if (i == 2) continue;

        //     if (i == 1) {
        //         for (var j = 1; j < data['maemae1'][i].length; j++) {
        //             if (data['maemae1'][i][j]) {
        //                 // 오래 걸려서 특정 지역만 보기
        //                 if (region && data['maemae1'][i][j].indexOf(region) == -1) continue;

        //                 chartTitleMaeJeon[j] = data['maemae1'][i][j];
        //             }
        //         }

        //         continue;
        //     }

        //     if (!data['maemae1'][i][0]) continue;

        //     // 연도 생성
        //     var date = data['maemae1'][i][0].split('/');

        //     if (date.length == 3) {
        //         year = date[0];
        //     } else {
        //         data['maemae1'][i][0] = year + '/' + data['maemae1'][i][0];
        //     }

        //     for (key in chartTitleMaeJeon) {
        //         if (!chartDataMaeJeon[key]) {
        //             chartDataMaeJeon[key] = [];
        //         }

        //         var jeonse = parseFloat(data['jeonse1'][i][key]);
        //         var maemae = parseFloat(data['maemae1'][i][key]);

        //         chartDataMaeJeon[key].push([data['maemae1'][i][0], maemae, jeonse]);
        //     }
        // }
    }

    // console.log(chartTitleJeonse);
    // console.log(chartDataJeonse);
    // console.log(chartTitleMaeJeon);
    // console.log(chartDataMaeJeon);
    // return false;

    // for (key in chartTitleMaeJeon) {
    //     console.log('?');
    //     chartType = 'ColumnChart';

    //     var data = new google.visualization.DataTable();
    //     data.addColumn('string', 'Date');
    //     data.addColumn('number', '매매');
    //     data.addColumn('number', '전세');
    //     data.addRows(chartDataMaeJeon[key]);

    //     var options = {
    //         'title': chartTitleMaeJeon[key],
    //         'legend': 'none',
    //         'width': getRowWidth(),
    //         'height': 300,
    //         vAxis: {
    //             viewWindow: {
    //                 max: 0.5,
    //                 min: -0.5
    //             }
    //         }
    //     };

    //     drawChart(data, options);
    // }

    for (key in chartTitle) {
        chartType = 'LineChart';

        // 심리차트 그리기
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Date');
        data.addColumn('number', '매도');
        data.addColumn('number', '매수');
        data.addRows(chartData[key]);

        var options = {
            'title': chartTitle[key] + ' (' + lastDate + ')',
            'legend': 'none',
            'width': getRowWidth(),
            'height': 300,
        };

        drawChart(data, options);

        // 전세수급차트 그리기
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Date');
        data.addColumn('number', '수요');
        data.addColumn('number', '공급');
        data.addColumn('number', '전세수급지수');
        data.addRows(chartDataJeonse[key]);

        var options = {
            'title': chartTitleJeonse[key] + ' (' + lastDate + ')',
            'legend': 'none',
            'width': getRowWidth(),
            'height': 300,
            'series': {
                2: {
                    'targetAxisIndex': 1
                }
            },
            // 오른쪽 축만 별도로 옵션
            'vAxes': {
                1: {
                    viewWindow: {
                        max: 200,
                        min: 0
                    }
                }
            }
        };

        drawChart(data, options);
    }
}

// 2. m1m2 차트
function renderm1m2(data) {
    // 데이터 준비
    if (data) {
        var chartData = {};
        var m1m2Sum = 0;
        var m1m2Average = 0;
        chartData['m1'] = [];
        chartData['m1m2'] = [];

        for (var i = 0; i < data['m1'].length; i++) {
            var m1m2Ratio = parseFloat((data['m1'][i].DT/data['m2'][i].DT).toFixed(2));
            m1m2Sum += m1m2Ratio;
            chartData['m1'].push([data['m1'][i].PRD_DE, parseFloat(data['m1'][i].DT)]);
            chartData['m1m2'].push([data['m1'][i].PRD_DE, m1m2Ratio]);
        }

        m1m2Average = parseFloat((m1m2Sum / data['m1'].length).toFixed(2));

        for (var i = 0; i < data['m1'].length; i++) {
            chartData['m1m2'][i].push(m1m2Average);
        }

        console.log(chartData);

        chartType = 'ComboChart';

        for (key in chartData) {
            var data = new google.visualization.DataTable();
            data.addColumn('string', 'Date');
            data.addColumn('number', key);

            if (key == 'm1m2') {
                data.addColumn('number', key);
            }

            data.addRows(chartData[key]);

            var options = {
                'title': key,
                'legend': 'none',
                'seriesType': 'bars',
                'width': getRowWidth(),
                'height': 300,
            };

            if (key == 'm1m2') {
                options.series = {1: {type: 'line'}};
            }

            drawChart(data, options);
        }
    }
}

// 3. 부동산전망 차트
function renderjeonmang(data) {
    if (!data) return false;

    var chartData = {};
    var chartTitle = {};
    var year = null;

    // 날짜 맞춤
    data['maemae'].splice(4, 33);

    for (var i = 0; i < data['maemae'].length; i++) {
        if (i == 0) continue;
        if (i == 2) continue;
        if (i == 3) continue;

        if (i == 1) {
            for (var j = 0; j < data['maemae'][i].length; j++) {
                if (data['maemae'][i][j]) {
                    chartTitle[j] = data['maemae'][i][j];
                }
            }

            continue;
        }

        if (!data['maemae'][i][0]) continue;

        // 연도 생성

        if (typeof data['maemae'][i][0] == 'string' && data['maemae'][i][0].indexOf('.') !== -1) {
            year = data['maemae'][i][0].split('.')[0]
            year = year.substring(1);
            data['maemae'][i][0] = data['maemae'][i][0].substring(1);
        } else {
            data['maemae'][i][0] = year + '.' + data['maemae'][i][0];
        }

        for (key in chartTitle) {
            if (!chartData[key]) {
                chartData[key] = [];
            }

            var maemae = parseFloat(data['maemae'][i][(parseInt(key) + 1)]) - parseFloat(data['maemae'][i][(parseInt(key) + 3)]);
            var jeonse = parseFloat(data['jeonse'][i][(parseInt(key) + 1)]) - parseFloat(data['jeonse'][i][(parseInt(key) + 3)]);

            maemae = parseFloat(maemae.toFixed(2));
            jeonse = parseFloat(jeonse.toFixed(2));

            chartData[key].push([data['maemae'][i][0], maemae, jeonse]);
        }
    }

    // console.log(chartTitle);
    // console.log(chartData);
    // return false;

    chartType = 'ColumnChart';

    for (key in chartTitle) {
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Date');
        data.addColumn('number', '매매');
        data.addColumn('number', '전세');
        data.addRows(chartData[key]);

        var options = {
            'title': chartTitle[key],
            'legend': 'none',
            'width': getRowWidth(),
            'height': 300,
            vAxis: {
                viewWindow: {
                    max: 100,
                    min: -100
                }
            }
        };

        drawChart(data, options);
    }
}

// 3. 부동산전망 차트2
function renderjeonmang2(data) {
    if (!data) return false;

    var chartData = {};
    var chartTitle = {};
    var year = null;
    var parentTitle = '';

    for (var i = 0; i < data['maemae'].length; i++) {
        if (i == 0) continue;
        if (i == 2) continue;

        if (i == 1) {
            for (var j = 1; j < data['maemae'][i].length; j++) {
                if (data['maemae'][i][j]) {
                    // 부모 붙이기
                    if (data['maemae'][i][j] != '대구' && (data['maemae'][i][j].slice(-1) == '구' || data['maemae'][i][j].slice(-1) == '군')) {
                        data['maemae'][i][j] = parentTitle + data['maemae'][i][j];
                    } else {
                        parentTitle = data['maemae'][i][j];
                    }

                    // 오래 걸려서 특정 지역만 보기
                    if (region && data['maemae'][i][j].indexOf(region) == -1) continue;

                    chartTitle[j] = data['maemae'][i][j];
                }
            }

            continue;
        }

        if (!data['maemae'][i][0]) continue;

        // 연도 생성
        var date = data['maemae'][i][0].split('/');

        if (date.length == 3) {
            year = date[0];
        } else {
            data['maemae'][i][0] = year + '/' + data['maemae'][i][0];
        }

        // 데이터가 많아서 올해 데이터만
        // if (year < 19) continue;

        // // 연도 생성
        // if (typeof data['maemae'][i][0] == 'string' && data['maemae'][i][0].indexOf('.') !== -1) {
        //     year = data['maemae'][i][0].split('.')[0]
        //     year = year.substring(1);
        //     data['maemae'][i][0] = data['maemae'][i][0].substring(1);
        // } else {
        //     data['maemae'][i][0] = year + '.' + data['maemae'][i][0];
        // }

        for (key in chartTitle) {
            if (!chartData[key]) {
                chartData[key] = [];
            }

            var jeonse = parseFloat(data['jeonse'][i][key]);
            var maemae = parseFloat(data['maemae'][i][key]);

            // maemae = parseFloat(maemae.toFixed(2));
            // jeonse = parseFloat(jeonse.toFixed(2));

            chartData[key].push([data['maemae'][i][0], maemae, jeonse]);
        }
    }

    // console.log(chartTitle);
    // console.log(chartData);
    // return false;

    chartType = 'ColumnChart';

    for (key in chartTitle) {
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Date');
        data.addColumn('number', '매매');
        data.addColumn('number', '전세');
        data.addRows(chartData[key]);

        var options = {
            'title': chartTitle[key],
            'legend': 'none',
            'width': getRowWidth(),
            'height': 300,
            vAxis: {
                viewWindow: {
                    max: 0.5,
                    min: -0.5
                }
            }
        };

        drawChart(data, options);
    }
}

// 4. 충전차트
function renderchoongjeon(data) {

    if (!data) return false;

    var chartData = {};
    var chartTitle = {};
    var year = null;

    for (var i = 0; i < data['jeonse'].length; i++) {
        if (i == 0) continue;
        if (i == 2) continue;

        if (i == 1) {
            for (var j = 1; j < data['jeonse'][i].length; j++) {
                if (data['jeonse'][i][j]) {
                    // 오래 걸려서 특정 지역만 보기
                    if (region && data['jeonse'][i][j].indexOf(region) == -1) continue;

                    chartTitle[j] = data['jeonse'][i][j];
                }
            }

            continue;
        }

        if (!data['jeonse'][i][0]) continue;

        // 연도 생성
        var date = data['jeonse'][i][0].split('/');

        if (date.length == 3) {
            year = date[0];
        } else {
            data['jeonse'][i][0] = year + '/' + data['jeonse'][i][0];
        }

        for (key in chartTitle) {
            if (!chartData[key]) {
                chartData[key] = [];
            }

            var jeonse = parseFloat(data['jeonse'][i][key]);
            var maemae = parseFloat(data['maemae'][i][key]);
            jeonse = parseFloat(jeonse.toFixed(2));
            maemae = parseFloat(maemae.toFixed(2));
            var choongjeon = parseFloat((jeonse - maemae).toFixed(2));

            // // 내가 생각하는 충전
            // var choongjeon = 0;
            // if (chartData[key].length > 0) {
            //     var lastElement = chartData[key][chartData[key].length - 1];

            //     maemarInc = (lastElement[2] - maemae);
            //     jeonseInc = (jeonse - lastElement[1]);

            //     if (maemarInc < 0) {
            //         choongjeon = lastElement[3] + maemarInc;
            //     } else {
            //         choongjeon = lastElement[3] + maemarInc + jeonseInc;
            //     }
            // }

            chartData[key].push([data['jeonse'][i][0], jeonse, maemae, choongjeon]);
        }
    }

    // console.log(chartTitle);
    // console.log(chartData);
    // return false;

    chartType = 'LineChart';
    var cnt = 0;

    for (key in chartTitle) {
        // cnt++;
        // if (cnt < 100) continue;
        // if (cnt > 110) break;

        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Date');
        data.addColumn('number', '전세');
        data.addColumn('number', '매매');
        data.addColumn('number', '충전');
        data.addRows(chartData[key]);

        var options = {
            'title': chartTitle[key],
            'legend': 'none',
            'width': getRowWidth(),
            'height': 300,
            'series': {
                2: {
                    'targetAxisIndex': 1
                }
            },
        };

        drawChart(data, options);
    }
}

// 5. 미분양차트
function rendermiboonyang(data) {
    if (!data) return false;

    var chartData = [];
    var index = 0;
    var min = 0;
    var max = 0;

    for (var i = 0; i < data.length; i++) {
        if (!chartData[index]) chartData[index] = [];

        data[i]['DT'] = parseInt(data[i]['DT']) + 100
        data[i]['C1_NM'] = data[i]['C1_NM'] + ' ' + data[i]['C2_NM'];

        // 추가 데이터
        if (!chartDetail[data[i]['C1_NM']]) {
            chartDetail[data[i]['C1_NM']] = data[i]['C1'] + '||' + data[i]['C2'];
        }

        chartData[index].push([data[i]['C1_NM'], data[i]['DT']]);

        if (min > data[i]['DT']) {
            min = data[i]['DT'];
        }

        if (max < data[i]['DT']) {
            max = data[i]['DT'];
        }

        if (i > 0 && i % 20 == 0) index++;
    }

    // console.log(chartData);

    chartType = 'ColumnChart';

    for (var i = 0; i < chartData.length; i++) {
        var data = new google.visualization.DataTable();
        data.addColumn('string', '시군구');
        data.addColumn('number', '미분양');
        data.addRows(chartData[i]);

        var options = {
            'title': '미분양차트',
            'legend': 'none',
            'width': getRowWidth(),
            'height': 300,
            vAxis: {
                viewWindow: {
                    max: max,
                    min: min
                }
            }
        };

        drawChart(data, options);
    }
    // if (!data) return false;

    // var chartData = {};

    // for (var i = 0; i < data.length; i++) {
    //     for (var j = 0; j < data[i].length; j++) {
    //         if (!chartData[data[i][j]['C1_NM']]) {
    //             chartData[data[i][j]['C1_NM']] = [];
    //         }

    //         chartData[data[i][j]['C1_NM']].push([data[i][j]['PRD_DE'], parseInt(data[i][j]['DT'])]);
    //     }
    // }

    // chartType = 'ColumnChart';

    // for (key in chartData) {
    //     var data = new google.visualization.DataTable();
    //     data.addColumn('string', 'Date');
    //     data.addColumn('number', key);
    //     data.addRows(chartData[key]);

    //     var options = {
    //         'title': key,
    //         'legend': 'none',
    //         'width': 1000,
    //         'height': 300,,
    //     };

    //     drawChart(data, options);
    // }
}

// 5-1. 미분양 지역상세
function rendermiboonyangDetail(data) {
    if (!data) return false;

    var chartData = {};

    for (var i = 0; i < data.length; i++) {
        data[i]['C1_NM'] = data[i]['C1_NM'] + ' ' + data[i]['C2_NM'];

        if (!chartData[data[i]['C1_NM']]) {
            chartData[data[i]['C1_NM']] = [];
        }

        chartData[data[i]['C1_NM']].push([data[i]['PRD_DE'], parseInt(data[i]['DT'])]);
    }

    chartType = 'ColumnChart';

    for (key in chartData) {
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Date');
        data.addColumn('number', '미분양');
        data.addRows(chartData[key]);

        var options = {
            'title': key,
            'legend': 'none',
            'width': getRowWidth(),
            'height': 300,
        };

        drawChart(data, options);
    }
}

// 6. 인구수 (최근 1개월 내)
function renderingoo(data) {
    if (!data) return false;

    var chartData = [];
    var index = 0;
    var min = 0;
    var max = 0;

    for (var i = 1; i < data.length; i++) {
        if (!chartData[index]) chartData[index] = [];

        data[i]['DT'] = parseInt(data[i]['DT'])

        // 추가 데이터
        if (!chartDetail[data[i]['C1_NM']]) {
            chartDetail[data[i]['C1_NM']] = data[i]['C1'];
        }

        chartData[index].push([data[i]['C1_NM'], data[i]['DT']]);

        if (min > data[i]['DT']) {
            min = data[i]['DT'];
        }

        if (max < data[i]['DT']) {
            max = data[i]['DT'];
        }

        if (i > 0 && i % 20 == 0) index++;
    }

    // console.log(chartData);

    chartType = 'ColumnChart';

    for (var i = 0; i < chartData.length; i++) {
        var data = new google.visualization.DataTable();
        data.addColumn('string', '시군구');
        data.addColumn('number', '인구수');
        data.addRows(chartData[i]);

        var options = {
            'title': '인구수',
            'legend': 'none',
            'width': getRowWidth(),
            'height': 300,
            vAxis: {
                viewWindow: {
                    max: max,
                    min: min
                }
            }
        };

        drawChart(data, options);
    }
}

// 6-1. 인구수차트 지역상세
function renderingooDetail(data) {
    if (!data) return false;

    var chartData = {};

    for (var i = 0; i < data['ingoo'].length; i++) {
        if (!chartData[data['ingoo'][i]['C1_NM']]) {
            chartData[data['ingoo'][i]['C1_NM']] = [];
        }

        chartData[data['ingoo'][i]['C1_NM']].push([data['ingoo'][i]['PRD_DE'], parseInt(data['ingoo'][i]['DT']), parseInt(data['sedae'][i]['DT'])]);
    }

    chartType = 'LineChart';

    for (key in chartData) {
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Date');
        data.addColumn('number', '인구수');
        data.addColumn('number', '세대수');
        data.addRows(chartData[key]);

        var options = {
            'title': key,
            'legend': 'none',
            'width': getRowWidth(),
            'height': 300,
            'series': {
                1: {
                    'targetAxisIndex': 1
                }
            },
        };

        drawChart(data, options);
    }
}

// 7. 인구이동차트(최근3개월)
function renderingooidong(data) {
    if (!data) return false;

    var chartData = [];
    var index = 0;
    var min = 0;
    var max = 0;

    for (var i = 0; i < data.length; i++) {
        if (!chartData[index]) chartData[index] = [];

        data[i]['DT'] = parseInt(data[i]['DT'])

        // 추가 데이터
        if (!chartDetail[data[i]['C1_NM']]) {
            chartDetail[data[i]['C1_NM']] = data[i]['C1'];
        }

        chartData[index].push([data[i]['C1_NM'], data[i]['DT']]);

        if (min > data[i]['DT']) {
            min = data[i]['DT'];
        }

        if (max < data[i]['DT']) {
            max = data[i]['DT'];
        }

        if (i > 0 && i % 20 == 0) index++;
    }

    // console.log(chartData);

    chartType = 'ColumnChart';

    for (var i = 0; i < chartData.length; i++) {
        var data = new google.visualization.DataTable();
        data.addColumn('string', '시군구');
        data.addColumn('number', '순이동');
        data.addRows(chartData[i]);

        var options = {
            'title': '인구이동차트(최근3개월)',
            'legend': 'none',
            'width': getRowWidth(),
            'height': 300,
            vAxis: {
                viewWindow: {
                    max: max,
                    min: min
                }
            }
        };

        drawChart(data, options);
    }
}

// 7-1. 인구이동차트 지역상세
function renderingooidongDetail(data) {
    if (!data) return false;

    var chartData = {};

    for (var i = 0; i < data.length; i++) {
        if (!chartData[data[i]['C1_NM']]) {
            chartData[data[i]['C1_NM']] = [];
        }

        chartData[data[i]['C1_NM']].push([data[i]['PRD_DE'], parseInt(data[i]['DT'])]);
    }

    chartType = 'ColumnChart';

    for (key in chartData) {
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Date');
        data.addColumn('number', key);
        data.addRows(chartData[key]);

        var options = {
            'title': key,
            'legend': 'none',
            'width': getRowWidth(),
            'height': 300,
        };

        drawChart(data, options);
    }
}

// 8. 평균연령
function renderage(data) {
    if (!data) return false;

    var chartData = {};
    var min = 0;
    var max = 0;
    var date = '';

    for (var i = 0; i < data.length; i++) {
        if (i == 0) continue;
        if (i == 1) {
            date = data[i][1];
            continue;
        }
        if (i == 2) continue;

        var codes = data[i][0].match(/\((.*)\)/);
        var name = '';
        var parent = 0;

        data[i][0] = data[i][0].substring(0, codes['index']);
        data[i][0] = data[i][0].split(' ').map(function(item) {
            if (item) {
                // 부모
                // console.log('parent', parent);
                // console.log('name', name);
                if (parent == 0) parent = name;
                else parent = parent + ' ' + name;

                name = item.trim();
                return name;
            }
        });

        // console.log(codes[1]);
        // console.log(name);

        data[i][3] = parseFloat(data[i][3])

        // // 추가 데이터
        // if (!chartDetail[data[i]['C1_NM']]) {
        //     chartDetail[data[i]['C1_NM']] = data[i]['C1'];
        // }

        // console.log(parent);
        // console.log(name);

        if (!chartData[parent]) chartData[parent] = [];
        chartData[parent].push([name, data[i][3]]);

        if (min > data[i][3]) {
            min = data[i][3];
        }

        if (max < data[i][3]) {
            max = data[i][3];
        }

        // if (i > 100) return false;
    }

    // console.log(chartData);
    // return false;

    chartType = 'ColumnChart';

    for (key in chartData) {
        // 오름차순 정렬
        chartData[key].sort(function (a, b) {
            if (a[1] > b[1]) {
                return 1;
            }
            if (a[1] < b[1]) {
                return -1;
            }

            return 0;
        });

        var data = new google.visualization.DataTable();
        data.addColumn('string', '읍면동');
        data.addColumn('number', '평균연령');
        data.addRows(chartData[key]);

        if (key == '0') title = '';
        else title = key

        var options = {
            'title': title + ' 평균연령 (' + date + ')',
            'legend': 'none',
            'width': getRowWidth(),
            'height': 300,
            vAxis: {
                viewWindow: {
                    max: max,
                    min: min
                }
            }
        };

        drawChart(data, options);
    }
}

// 9. 아파트
function renderapt(data) {
    if (!data) return false;

    var html = '<select class="form-control" id="apt-sigoongoo">';
    html    += '<option value="">- 시군구 -</option>';

    for (var i = 0; i < data.length; i++) {
        html += '<option value="' + data[i]['code_sigoongoo'] + '">' + data[i]['sigoongoo'] + '</option>';
        html += '<li class="breadcrumb-item"><a href="#" class="sigoongoo" data-code="' + data[i]['C1'] + '">' + data[i]['C1_NM'] + '</a></li>';
    }

    html += '</select>';

    $('.wrap-chart').html(html);
}

// 9-1. 아파트 랭킹 렌더링
function renderaptSiRank(data) {
    if (!data) return false;
    var dataOrg = data;

    var chartData = [];
    var chartDataGap = [];
    var lastDate = 0;

    for (var i = 0; i < data.length; i++) {
        var aptName = data[i]['name_apt'] + ' (' + data[i]['upmyeondong'] + ', ' + data[i]['year_build'] + '년)';

        // 추가 데이터
        if (!chartDetail[aptName]) {
            chartDetail[aptName] = data[i]['id'];
        }

        // 아파트 상세정보
        var detailText = data[i]['name_apt'] + ' (' + data[i]['upmyeondong'] + ', ' + data[i]['year_build'] + '년)\n';
        var valueGap = [];
        var maeJeonGap = [];
        var valueGapAvg = 0;
        var maeJeonGapAvg = 0;

        for (var j = 0; j < data[i]['detail'].length; j++) {
            // 84와 가까운 기준평수로 매전갭, 가치를 계산한다.
            if (data[i]['detail'][j]['price_per_size'] == data[i]['price']) {
                maeJeonGapAvg = data[i]['detail'][j]['sale_price'] - data[i]['detail'][j]['jeonse_price'];
                valueGapAvg = data[i]['detail'][j]['values'][1] - data[i]['detail'][j]['values'][0];
            }
            if (data[i]['detail'][j]['sale_price'] > 0 && data[i]['detail'][j]['jeonse_price'] > 0) {
                maeJeonGap.push(data[i]['detail'][j]['sale_price'] - data[i]['detail'][j]['jeonse_price']);
            }
            if (data[i]['detail'][j]['price_per_size'] == data[i]['price']) {
                detailText += '*'
            }
            detailText += data[i]['detail'][j]['size'] + '㎡' + ' (' + data[i]['detail'][j]['date'] + ') ';
            detailText += '매)' + numberWithCommas(data[i]['detail'][j]['sale_price']) + '-전)' + numberWithCommas(data[i]['detail'][j]['jeonse_price']) + '=갭)' + numberWithCommas(parseInt(data[i]['detail'][j]['sale_price']) - parseInt(data[i]['detail'][j]['jeonse_price']));

            if (data[i]['detail'][j]['values'] && data[i]['detail'][j]['values'][0] > 0) {
                detailText +=  ' / 가치(' + data[i]['detail'][j]['values'][0] + '/' + data[i]['detail'][j]['values'][1] + ')';
                valueGap.push(data[i]['detail'][j]['values'][1] - data[i]['detail'][j]['values'][0]);
            }
            detailText += '\n';

            if (lastDate < data[i]['detail'][j]['date']) {
                lastDate = data[i]['detail'][j]['date'];
            }
        }

        var average = arr => arr.reduce( ( p, c ) => p + c, 0 ) / arr.length;

        if (valueGapAvg == 0) valueGapAvg = average(valueGap);
        if (maeJeonGapAvg == 0) maeJeonGapAvg = average(maeJeonGap);

        var valueColor = '#F5B7B1';

        if (valueGapAvg > 5) valueColor = '#0E6251';
        else if (valueGapAvg > 4) valueColor = '#117864';
        else if (valueGapAvg > 3) valueColor = '#148F77';
        else if (valueGapAvg > 2) valueColor = '#17A589';
        else if (valueGapAvg > 1.5) valueColor = '#1ABC9C';
        else if (valueGapAvg > 1) valueColor = '#48C9B0';
        else if (valueGapAvg > 0.5) valueColor = '#76D7C4';
        else if (valueGapAvg >= 0) valueColor = '#A3E4D7';
        else if (valueGapAvg < -5) valueColor = '#78281F';
        else if (valueGapAvg < -4) valueColor = '#943126';
        else if (valueGapAvg < -3) valueColor = '#B03A2E';
        else if (valueGapAvg < -2) valueColor = '#CB4335';
        else if (valueGapAvg < -1.5) valueColor = '#E74C3C';
        else if (valueGapAvg < -1) valueColor = '#EC7063';
        else if (valueGapAvg < -0.5) valueColor = '#F1948A';
        else if (valueGapAvg < -0.2) valueColor = '#F5B7B1';

        var valueColorMae = '#0E6251';

        if (maeJeonGapAvg > 40000) valueColorMae = '#78281F';
        else if (maeJeonGapAvg > 30000) valueColorMae = '#943126';
        else if (maeJeonGapAvg > 20000) valueColorMae = '#E74C3C';
        else if (maeJeonGapAvg > 15000) valueColorMae = '#F5B7B1';
        else if (maeJeonGapAvg > 10000) valueColorMae = '#48C9B0';
        else if (maeJeonGapAvg > 6000) valueColorMae = '#76D7C4';
        else if (maeJeonGapAvg > 3000) valueColorMae = '#1ABC9C';
        else if (maeJeonGapAvg > 1000) valueColorMae = '#148F77';

        chartData.push([aptName, parseInt(data[i]['price_before']), detailText, valueColor, parseInt(data[i]['price'] - data[i]['price_before']), detailText, valueColor]);
        chartDataGap.push([aptName, parseInt(maeJeonGapAvg), valueColorMae]);
    }

    // 날짜만들기
    var startYear = parseInt(lastDate.substring(0, 4));
    var startMonth = parseInt(lastDate.substring(4, 6)) - 1;
    var beforeYear = startYear - 3 // 3년전까지
    var html = '<div class="mt-2 mb-2 col-sm"><select class="form-control" id="apt-date">';

    for (var d = new Date(startYear, startMonth, 1); d >= new Date(beforeYear, startMonth, 1); d.setMonth(d.getMonth() - 1)) {
        var year = d.getFullYear();
        var month = d.getMonth() + 1;
        month = (month > 9 ? '' : '0') + month;

        ym = year + '' + month;

        html += '<option value="' + ym + '">' + ym + '</option>';
    }

    html += '</select></div>';

    $('.wrap-chart').append(html);

    chartType = 'ColumnChart';

    var data = new google.visualization.DataTable();
    data.addColumn('string', '아파트');
    data.addColumn('number', '이전가격');
    data.addColumn({type: 'string', role: 'tooltip'});
    data.addColumn({type: 'string', role: 'style'});
    data.addColumn('number', '오른가격');
    data.addColumn({type: 'string', role: 'tooltip'});
    data.addColumn({type: 'string', role: 'style'});
    data.addRows(chartData);

    var options = {
        'title': '평당가격 순위',
        'legend': 'none',
        'width': getRowWidth(),
        'height': 300,
        'isStacked': true,
        // 'focusTarget': 'category'
    };

    drawChart(data, options);

    var data = new google.visualization.DataTable();
    data.addColumn('string', '아파트');
    data.addColumn('number', '매전차액');
    data.addColumn({type: 'string', role: 'style'});
    data.addRows(chartDataGap);

    var options = {
        'title': '매전차액',
        'legend': 'none',
        'width': getRowWidth(),
        'height': 300,
    };

    drawChart(data, options);

    // 이전
    if (rankData) {
        randerRankComp(dataOrg);
    }

    // 전, 후 비교를 위해 저장해둔다.
    if (!rankData) {
        rankData = dataOrg;
    }
}

// 9-1. 아파트 랭킹 렌더링
function randerRankComp(data) {
    if (!data) return false;
    if (!rankData) return false;

    var chartData = [];
    var chartDataGap = [];

    for (var i = 0; i < data.length; i++) {
        var isFounded = false;
        var aptName = data[i]['name_apt'] + ' (' + data[i]['upmyeondong'] + ', ' + data[i]['year_build'] + '년)';

        for (var k = 0; k < rankData.length; k++) {

            if (data[i]['id'] != rankData[k]['id']) continue;

            isFounded = true;

            // 추가 데이터
            if (!chartDetail[aptName]) {
                chartDetail[aptName] = data[i]['id'];
            }

            // 아파트 상세정보
            var detailText = data[i]['name_apt'] + ' (' + data[i]['upmyeondong'] + ', ' + data[i]['year_build'] + '년)\n';
            var maeJeonGap = [];
            var maeJeonGapAvg = 0;
            var priceIncs = [];
            var priceInc = 0;
            var priceBuy = 0;
            var priceSell = 0;

            for (var j = 0; j < data[i]['detail'].length; j++) {
                // 84와 가까운 기준평수로 매전갭, 가치를 계산한다.
                if (data[i]['detail'][j]['price_per_size'] == data[i]['price']) {
                    maeJeonGapAvg = data[i]['detail'][j]['sale_price'] - data[i]['detail'][j]['jeonse_price'];
                    priceInc = rankData[k]['detail'][j]['sale_price'] - data[i]['detail'][j]['sale_price'];
                    priceBuy = data[i]['detail'][j]['sale_price'];
                    priceSell = rankData[k]['detail'][j]['sale_price'];
                }
                // 매전갭
                if (data[i]['detail'][j]['sale_price'] > 0 && data[i]['detail'][j]['jeonse_price'] > 0) {
                    maeJeonGap.push(data[i]['detail'][j]['sale_price'] - data[i]['detail'][j]['jeonse_price']);
                    priceIncs.push(rankData[k]['detail'][j]['sale_price'] - data[i]['detail'][j]['sale_price']);
                    priceIncs.push(rankData[k]['detail'][j]['sale_price'] - data[i]['detail'][j]['sale_price']);
                }
                detailText += '*' + data[i]['detail'][j]['size'] + '㎡' + ' (' + rankData[k]['detail'][j]['date'] + ') ';
                detailText += '현)' + numberWithCommas(rankData[k]['detail'][j]['sale_price']) + '-과)' + numberWithCommas(data[i]['detail'][j]['sale_price']) + '=차액)' + numberWithCommas(parseInt(rankData[k]['detail'][j]['sale_price']) - parseInt(data[i]['detail'][j]['sale_price']));
                detailText += '\n';
            }

            var average = arr => arr.reduce( ( p, c ) => p + c, 0 ) / arr.length;

            if (maeJeonGapAvg == 0) {
                maeJeonGapAvg = average(maeJeonGap);
            }

            // 매전갭에 기타부대비용 합산
            chidc = parseInt(priceBuy * 0.011); // 취등록세
            bokbi1 = parseInt(priceBuy * 0.008); // 매수전세복비
            bokbi2 = parseInt(priceSell * 0.004); // 매전세복비
            bubmoo = 60; // 법무사비
            jaesanse = 100; // 재산세

            maeJeonGapAvg = maeJeonGapAvg + bokbi1 + bokbi2 + bubmoo + jaesanse;

            var priceIncRatio = 0

            if (priceInc == 0) {
                priceInc = average(priceIncs);
            }

            if (priceInc != 0 && maeJeonGapAvg != 0) {
                priceIncRatio = (priceInc / maeJeonGapAvg).toFixed(2);
            }

            // 최대값 4
            if (priceIncRatio > 10) priceIncRatio = 10;
            if (maeJeonGapAvg < 0 && priceIncRatio > 0) priceIncRatio = 10;
            if (priceIncRatio < 0) priceIncRatio = 0;

            var valueColor = '#A3E4D7';

            if (priceInc > 300) valueColor = '#0E6251';
            else if (priceInc > 210) valueColor = '#117864';
            else if (priceInc > 180) valueColor = '#148F77';
            else if (priceInc > 150) valueColor = '#17A589';
            else if (priceInc > 120) valueColor = '#1ABC9C';
            else if (priceInc > 90) valueColor = '#48C9B0';
            else if (priceInc > 60) valueColor = '#76D7C4';
            else if (priceInc > 30) valueColor = '#A3E4D7';


            var valueColorMae = '#A3E4D7';

            if (priceIncRatio > 5) valueColorMae = '#0E6251';
            else if (priceIncRatio > 4) valueColorMae = '#117864';
            else if (priceIncRatio > 3) valueColorMae = '#148F77';
            else if (priceIncRatio > 2.5) valueColorMae = '#17A589';
            else if (priceIncRatio > 2) valueColorMae = '#1ABC9C';
            else if (priceIncRatio > 1.5) valueColorMae = '#48C9B0';
            else if (priceIncRatio > 1) valueColorMae = '#76D7C4';
            else if (priceIncRatio > 0.5) valueColorMae = '#A3E4D7';

            chartData.push([aptName, parseInt(priceInc), detailText, valueColor]);
            chartDataGap.push([aptName, parseFloat(priceIncRatio), valueColorMae]);

            break;
        }

        if (!isFounded) {
            chartData.push([aptName, 0, '', '#0E6251']);
            chartDataGap.push([aptName, 0, '#0E6251']);
        }
    }

    chartType = 'ColumnChart';

    var data = new google.visualization.DataTable();
    data.addColumn('string', '아파트');
    data.addColumn('number', '상승가격');
    data.addColumn({type: 'string', role: 'tooltip'});
    data.addColumn({type: 'string', role: 'style'});
    data.addRows(chartData);

    var options = {
        'title': '아파트 상승가격',
        'legend': 'none',
        'width': getRowWidth(),
        'height': 300,
        // 'focusTarget': 'category'
    };

    drawChart(data, options);

    var data = new google.visualization.DataTable();
    data.addColumn('string', '아파트');
    data.addColumn('number', '투자금대비 상승');
    data.addColumn({type: 'string', role: 'style'});
    data.addRows(chartDataGap);

    var options = {
        'title': '투자금대비 상승',
        'legend': 'none',
        'width': getRowWidth(),
        'height': 300,
    };

    drawChart(data, options);
}

// 9-2. 아파트 매매/전세, 가치 렌더링
function renderaptDetail(data) {
    if (!data) return false;

    var startYm = data.price[0].date;
    var priceDefault = {};
    var priceDates = {};
    var vAxisRange = {};

    var now = new Date();
    var month = now.getMonth() + 1;
    month = (month > 9 ? '' : '0') + month;

    for (var i = 0; i < data.price.length; i++) {
        if (!priceDefault[data.price[i]['size']]) priceDefault[data.price[i]['size']] = {'jeonse_price': 0, 'jeonse_price_max': 0, 'jeonse_price_min': 0, 'sale_price': 0, 'sale_price_max': 0, 'sale_price_min': 0};
        if (!priceDates[data.price[i]['size']]) priceDates[data.price[i]['size']] = {};

        if (!priceDefault[data.price[i]['size']]['jeonse_price'] && data.price[i]['jeonse_price'] > 0) priceDefault[data.price[i]['size']]['jeonse_price'] = data.price[i]['jeonse_price'];
        if (!priceDefault[data.price[i]['size']]['jeonse_price_max'] && data.price[i]['jeonse_price_max'] > 0) priceDefault[data.price[i]['size']]['jeonse_price_max'] = data.price[i]['jeonse_price_max'];
        if (!priceDefault[data.price[i]['size']]['jeonse_price_min'] && data.price[i]['jeonse_price_min'] > 0) priceDefault[data.price[i]['size']]['jeonse_price_min'] = data.price[i]['jeonse_price_min'];
        if (!priceDefault[data.price[i]['size']]['sale_price'] && data.price[i]['sale_price'] > 0) priceDefault[data.price[i]['size']]['sale_price'] = data.price[i]['sale_price'];
        if (!priceDefault[data.price[i]['size']]['sale_price_max'] && data.price[i]['sale_price_max'] > 0) priceDefault[data.price[i]['size']]['sale_price_max'] = data.price[i]['sale_price_max'];
        if (!priceDefault[data.price[i]['size']]['sale_price_min'] && data.price[i]['sale_price_min'] > 0) priceDefault[data.price[i]['size']]['sale_price_min'] = data.price[i]['sale_price_min'];

        priceDates[data.price[i]['size']][data.price[i]['date']] = data.price[i];
    }

    // console.log(priceDefault);

    var yms = getYmArrayAfterYm(startYm);
    var countSale = {};
    var titleCountOrg = data.info.name_apt + ' 거래횟수';

    for (var size in priceDefault) {
        var py = Math.floor((size / 3.3) + 7.5);
        var titlePrice = data.info.name_apt + ' 매매/전세' + '(' + py + '평/' + size + '㎡)';
        var titleEnergy = data.info.name_apt + ' 가치차트' + '(' + py + '평/' + size + '㎡)';
        var energies = [];

        priceDefault[size]['chartData'] = {};
        priceDefault[size]['chartData'][titlePrice] = [];
        priceDefault[size]['chartData'][titleEnergy] = [];
        vAxisRange[size] = [99999999999, 0];

        for (var i = 0; i < yms.length; i++) {
            if (priceDates[size][yms[i]]) {
                if (priceDates[size][yms[i]]['jeonse_price'] > 0) priceDefault[size]['jeonse_price'] = priceDates[size][yms[i]]['jeonse_price'];
                if (priceDates[size][yms[i]]['sale_price'] > 0) priceDefault[size]['sale_price'] = priceDates[size][yms[i]]['sale_price'];
                // if (priceDates[size][yms[i]]['jeonse_price_max'] > 0) priceDefault[size]['jeonse_price_max'] = priceDates[size][yms[i]]['jeonse_price_max'];
                // if (priceDates[size][yms[i]]['jeonse_price_min'] > 0) priceDefault[size]['jeonse_price_min'] = priceDates[size][yms[i]]['jeonse_price_min'];
                // if (priceDates[size][yms[i]]['sale_price_max'] > 0) priceDefault[size]['sale_price_max'] = priceDates[size][yms[i]]['sale_price_max'];
                // if (priceDates[size][yms[i]]['sale_price_min'] > 0) priceDefault[size]['sale_price_min'] = priceDates[size][yms[i]]['sale_price_min'];
                if (vAxisRange[size][0] > priceDefault[size]['jeonse_price']) vAxisRange[size][0] = priceDefault[size]['jeonse_price'];
                if (vAxisRange[size][1] < priceDefault[size]['sale_price']) vAxisRange[size][1] = priceDefault[size]['sale_price'];
            }

            priceDefault[size]['chartData'][titlePrice].push([
                yms[i],
                parseInt(priceDefault[size]['sale_price']),
                // parseInt(priceDefault[size]['sale_price_max']),
                // parseInt(priceDefault[size]['sale_price_min']),
                parseInt(priceDefault[size]['jeonse_price']),
                // parseInt(priceDefault[size]['jeonse_price_max']),
                // parseInt(priceDefault[size]['jeonse_price_min']),
                // (parseInt(priceDefault[size]['sale_price']) - parseInt(priceDefault[size]['jeonse_price'])),
            ]);

            if (!countSale[yms[i]]) countSale[yms[i]] = [0, 0];
            countSale[yms[i]][0] = countSale[yms[i]][0] + parseInt((priceDates[size][yms[i]] && priceDates[size][yms[i]]['sale_count'] || 0));
            countSale[yms[i]][1] = countSale[yms[i]][1] + parseInt((priceDates[size][yms[i]] && priceDates[size][yms[i]]['jeonse_count'] || 0));

            // 이번 달과 같다면 가격 추출
            if (yms[i].substring(4, 6) == month) {
                var year = yms[i].substring(0, 4);
                var energy = getEnergy(year, priceDefault[size]['sale_price']);

                if (energy) {
                    energy = parseFloat(energy);

                    priceDefault[size]['chartData'][titleEnergy].push([
                        year,
                        energy,
                    ]);

                    energies.push(energy);
                }
            }
        }

        if (energies.length > 0) {
            var sumEnergy = energies.reduce(function(a, b) { return a + b; });
            var averageEnergy = (sumEnergy / energies.length).toFixed(2);

            // console.log(energies);
            // console.log(sumEnergy);
            // console.log(averageEnergy);

            for (var i = 0; i < priceDefault[size]['chartData'][titleEnergy].length; i++) {
                priceDefault[size]['chartData'][titleEnergy][i][2] = parseFloat(averageEnergy);
            }
        }
    }

    console.log(priceDefault);

    for (var size in priceDefault) {
        var index = 0;
        console.log(priceDefault[size]);
        for (var title in priceDefault[size]['chartData']) {
            console.log(priceDefault[size]['chartData'][title]);

            if (title.indexOf('매매/전세') != -1) {
                chartType = 'LineChart';

                var data = new google.visualization.DataTable();
                data.addColumn('string', 'Date');
                data.addColumn('number', '매매평균');
                // data.addColumn('number', '매매상위');
                // data.addColumn('number', '매매하위');
                data.addColumn('number', '전세평균');
                // data.addColumn('number', '전세상위');
                // data.addColumn('number', '전세하위');
                // data.addColumn('number', '갭');
                data.addRows(priceDefault[size]['chartData'][title]);

                var options = {
                    'title': title,
                    'width': getRowWidth(),
                    'height': 300,
                    // 'series': {
                    //     2: {
                    //         'targetAxisIndex': 1,
                    //         'lineDashStyle': [1, 3]
                    //     }
                    // },
                    'vAxis': {
                        viewWindow:{
                            min: parseInt(vAxisRange[size][0]) - 1000,
                            max: parseInt(vAxisRange[size][1]) + 1000,
                        }
                    },
                    'curveType': 'function',
                };

                console.log(options);

                drawChart(data, options);
            // 가치
            } else if (title.indexOf('가치차트') != -1) {
                chartType = 'LineChart';

                var data = new google.visualization.DataTable();
                data.addColumn('string', 'Date');
                data.addColumn('number', '가치');
                data.addColumn('number', '평균');
                data.addRows(priceDefault[size]['chartData'][title]);

                var options = {
                    'title': title,
                    'width': getRowWidth(),
                    'height': 300,
                };

                drawChart(data, options);
            }

            index++;
        }
    }

    countSales = [];

    for (key in countSale) {
        countSales.push([
            key,
            parseInt(countSale[key][0]),
            parseInt(countSale[key][1]),
        ]);
    }

    // 거래빈도
    chartType = 'ColumnChart';

    var data = new google.visualization.DataTable();
    data.addColumn('string', 'Date');
    data.addColumn('number', '매매거래');
    data.addColumn('number', '전세거래');
    data.addRows(countSales);

    var options = {
        'title': titleCountOrg,
        'width': getRowWidth(),
        'height': 300,
    };

    drawChart(data, options);
}

// 10. 아찌
function renderazzi(data) {
    if (!data) return false;

    var chartData = {};
    var year = null;
    var beforePrice = {};

    for (apt in data) {
        // 초기화
        chartData[apt] = {};
        chartData[apt][apt + ' 거래량'] = []
        chartData[apt][apt + ' 평균가격'] = []
        beforePrice[apt] = {};

        // 날짜별로 정렬
        var dataSort = {};

        Object.keys(data[apt]).sort().forEach(function(key) {
            dataSort[key] = data[apt][key];
        });

        for (date in dataSort) {
            // 거래량 데이터
            chartData[apt][apt + ' 거래량'].push([date, parseFloat(data[apt][date]['total_prices']), parseFloat(data[apt][date]['total_jeonses']), parseFloat(data[apt][date]['total_prices_complete']), parseFloat(data[apt][date]['total_jeonses_complete'])]);

            var sizeAll1 = Object.keys(data[apt][date]['prices']);
            var sizeAll2 = Object.keys(data[apt][date]['jeonses']);
            var sizeAll = sizeAll1.concat(sizeAll2.filter((item) => sizeAll1.indexOf(item) < 0));

            var zeroDataTotal = [];

            for (var i = 0; i < sizeAll.length; i++) {
                // 초기화
                var py = Math.floor(sizeAll[i] / 3.3);
                var title1 = apt + ' 매매 ' + '(' + py + '평/' + sizeAll[i] + '㎡)';
                var title2 = apt + ' 전세 ' + '(' + py + '평/' + sizeAll[i] + '㎡)';

                if (!chartData[apt][title1]) {
                    chartData[apt][title1] = [];
                }
                if (!chartData[apt][title2]) {
                    chartData[apt][title2] = [];
                }

                var zeroData1 = [0, 0, 0, 0];
                var zeroData2 = [0, 0, 0, 0];

                if (data[apt][date]['prices'][sizeAll[i]]) {
                    if (!beforePrice[apt][title1]) {
                        beforePrice[apt][title1] = parseFloat(data[apt][date]['prices'][sizeAll[i]][1]);
                    }

                    zeroData1[0] = parseFloat(data[apt][date]['prices'][sizeAll[i]][0]);
                    zeroData1[1] = beforePrice[apt][title1];
                    zeroData1[2] = parseFloat(data[apt][date]['prices'][sizeAll[i]][1]);
                    zeroData1[3] = parseFloat(data[apt][date]['prices'][sizeAll[i]][2]);

                    beforePrice[apt][title1] = zeroData1[2];

                    if (zeroData1[2] > 0) {
                        zeroDataTotal.push((zeroData1[2] / py));
                    }
                }

                if (data[apt][date]['jeonses'][sizeAll[i]]) {
                    if (!beforePrice[apt][title2]) {
                        beforePrice[apt][title2] = parseFloat(data[apt][date]['jeonses'][sizeAll[i]][1]);
                    }

                    zeroData2[0] = parseFloat(data[apt][date]['jeonses'][sizeAll[i]][0]);
                    zeroData2[1] = beforePrice[apt][title2];
                    zeroData2[2] = parseFloat(data[apt][date]['jeonses'][sizeAll[i]][1]);
                    zeroData2[3] = parseFloat(data[apt][date]['jeonses'][sizeAll[i]][2]);

                    beforePrice[apt][title2] = zeroData2[2];
                }

                chartData[apt][title1].push([
                    date,
                    zeroData1[0],
                    zeroData1[1],
                    zeroData1[2],
                    zeroData1[3],
                ]);

                chartData[apt][title2].push([
                    date,
                    zeroData2[0],
                    zeroData2[1],
                    zeroData2[2],
                    zeroData2[3],
                ]);
            }

            var average = arr => arr.reduce( ( p, c ) => p + c, 0 ) / arr.length;
            var zeroDataTotalPrice = parseInt(average(zeroDataTotal));

            chartData[apt][apt + ' 평균가격'].push([
                date,
                zeroDataTotalPrice
            ]);
        }

        // break;
    }

    console.log(chartData);

    for (apt in chartData) {
        for (title in chartData[apt]) {
            if (title.indexOf('거래량') != -1) {
                chartType = 'LineChart';

                var data = new google.visualization.DataTable();
                data.addColumn('string', 'Date');
                data.addColumn('number', '매물(매매)');
                data.addColumn('number', '매물(전세)');
                data.addColumn('number', '거래완료(매매)');
                data.addColumn('number', '거래완료(전세)');
                data.addRows(chartData[apt][title]);

                var options = {
                    'title': title,
                    'width': getRowWidth(),
                    'height': 300,
                };

                drawChart(data, options);
            // 평균매매가격
            } else if (title.indexOf('평균가격') != -1) {
                chartType = 'LineChart';

                var data = new google.visualization.DataTable();
                data.addColumn('string', 'Date');
                data.addColumn('number', '평균매매가격');
                data.addRows(chartData[apt][title]);

                var options = {
                    'title': title,
                    'width': getRowWidth(),
                    'height': 300,
                };

                drawChart(data, options);
            // 매매/전세
            } else if (title.indexOf('매매') != -1 || title.indexOf('전세') != -1) {
                chartType = 'CandlestickChart';

                var data = new google.visualization.DataTable();
                data.addColumn('string', 'Date');
                data.addColumn('number', '최저가');
                data.addColumn('number', '시작가');
                data.addColumn('number', '종료가');
                data.addColumn('number', '최고가');
                data.addRows(chartData[apt][title]);

                var options = {
                    'title': title,
                    'width': getRowWidth(),
                    'height': 300,
                    'legend': 'none',
                    'candlestick': {
                        'fallingColor': { strokeWidth: 0, fill: '#a52714' }, // red
                        'risingColor': { strokeWidth: 0, fill: '#0000FF' }   // green
                    }
                };

                drawChart(data, options);
            }
        }

        // return false;
    }
}

// breadcrumb sample
// function renderage(data) {
//     if (!data) return false;

//     var html = '<nav aria-label="breadcrumb"><ol class="breadcrumb">';

//     for (var i = 0; i < data.length; i++) {

//         html += '<li class="breadcrumb-item"><a href="#" class="sigoongoo" data-code="' + data[i]['C1'] + '">' + data[i]['C1_NM'] + '</a></li>';
//     }

//     html += '</ol></nav>';

//     $('.wrap-chart').html(html);
// }

function drawChart(data, options) {
    if (!data) return false;
    if (!options) return false;

    insertChart();

    var chart = new google.visualization[chartType]($('.chart-empty')[0]);

    chart.draw(data, options);

    // 차트 클릭
    google.visualization.events.addListener(chart, 'select', function() {
        var selection = chart.getSelection();

        var message = '';

        for (var i = 0; i < selection.length; i++) {
            var item = selection[i];
            var extra = chartDetail[data.getValue(item.row, 0)];

            console.log(data.getValue(item.row, 0))
            console.log(extra);

            if (extra && window['render' + menu + 'Detail']) {

                $.ajax({
                    type: "POST",
                    url: '../api/yoona.php',
                    dataType : 'json',
                    cache: false,
                    timeout: 100000,
                    data: {
                        menu: menu + '_detail',
                        extra: extra
                    },
                    success: function (result, textStatus) {
                        console.log(result);
                        console.log(textStatus);
                        window['render' + menu + 'Detail'](result);
                    },
                    error: function(result, textStatus, jqXHR) {
                        console.log(result);
                        console.log(textStatus);
                        console.log(jqXHR);
                    },
                    complete: function() {
                    }
                });
            }

            // console.log(data.getValue(item.row, 0));
            // console.log(data.getValue(0, item.column));
            // console.log(data.getValue(item.row, item.column));
        }
    });

    $('.chart-empty').eq(0).removeClass('chart-empty');
}

function insertChart() {
    if ($('.chart-empty').length > 0) return false;

    var html = '';

    html += '<div class="row">';
    html += '<div class="mt-2 mb-2 col-sm"><div class="chart chart-empty"></div></div>';
    html += '</div> ';

    $('.wrap-chart').append(html);
}

function clearChart() {
    $('.wrap-chart').html('');
}

function getYmArrayAfterYm(startYm) {
    if (!startYm) return false;

    var startYear = parseInt(startYm.substring(0, 4));
    var startMonth = parseInt(startYm.substring(4, 6)) - 1;

    var now = new Date();
    var ym = [];

    for (var d = new Date(startYear, startMonth, 1); d <= now; d.setMonth(d.getMonth() + 1)) {
        var year = d.getFullYear();
        var month = d.getMonth() + 1;
        month = (month > 9 ? '' : '0') + month;
        ym.push(year + month);
    }

    return ym;
}

function getEnergy(year, price) {
    if (!year) return false;
    if (!price) return false;

    energy = 0;

    // 연평균소득
    if (year == '2004') energy = price / 37349688;
    else if (year == '2005') energy = price / 39025080;
    else if (year == '2006') energy = price / 41328648;
    else if (year == '2007') energy = price / 43874412;
    else if (year == '2008') energy = price / 46807464;
    else if (year == '2009') energy = price / 46238268;
    else if (year == '2010') energy = price / 48092052;
    else if (year == '2011') energy = price / 50983428;
    else if (year == '2012') energy = price / 53908368;
    else if (year == '2013') energy = price / 55274592;
    else if (year == '2014') energy = price / 56815236;
    else if (year == '2015') energy = price / 57799980;
    else if (year == '2016') energy = price / 58613376;
    else if (year == '2017') energy = price / 60031080;
    else if (year == '2018') energy = price / 61531857;
    else if (year == '2019') energy = price / 63070153;
    else if (year == '2020') energy = price / 64331556;
    else if (year == '2021') energy = price / 65618187;

    return (energy * 10000).toFixed(1)
}

function getRowWidth() {
    return $('.row:eq(0)').width();
}

function numberWithCommas(x) {
    if (!x) return x;
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

// function getYArrayAfterY(startY) {
//     if (!startY) return false;

//     var startYear = parseInt(startY.substring(0, 4));

//     var now = new Date();
//     var y = [];

//     for (var d = new Date(startYear, 0, 1); d <= now; d.setYear(d.getYear() + 1)) {
//         var year = d.getFullYear();
//         y.push(year);
//     }

//     return y;
// }

$(document).ready(function() {
    $(document).on('click', '.list-menu', clickMenu);
    $(document).on('click', '.sigoongoo', clickSigoongoo);
    $(document).on('change', '#apt-sigoongoo', function (){
        var code = $(this).val();
        if (!code) return false;
        rankData = null;
        callApi({menu: 'apt_rank', extra: code}, renderaptSiRank);
    });
    $(document).on('change', '#apt-date', function (){
        var date = $(this).val();
        var code = $("#apt-sigoongoo").val();
        if (!date) return false;
        callApi({menu: 'apt_rank', extra: code, extra2: date}, renderaptSiRank);
    });
    // $('.list-menu').on('click', clickMenu);


    // Load the Visualization API and the corechart package.
    google.charts.load('current', {'packages':['corechart']});

    // // Set a callback to run when the Google Visualization API is loaded.
    // google.charts.setOnLoadCallback(drawChart);
});
</script>

</html>