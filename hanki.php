<?php
ini_set("memory_limit" , -1);
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_GET && $_GET['excel'] == 1) {
  include_once($_SERVER['DOCUMENT_ROOT'].'/class/hanki.php');
  $hanki = new Hanki();
  $hanki->excelOption();
  exit();
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>정성한끼 메뉴표</title>
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
  background-color: #fff;
}

.chart {
    text-align: center;
}
.chart > div {
    display: inline-block;
}

.wrap-chart,
.table {
  width: 100%;
}

.table.print {
  table-layout: fixed;
  border-collapse: collapse;
  border-style: hidden;
  border-top: 1px solid #dee2e6;
  background-color: #fff;
}

.table.print th {
  text-align: center;
  font-size:2.5rem;
}

.table.print td {
  border: 1px solid #dee2e6;
}

.table.print td p {
  margin-bottom: 0.1rem;
  text-align: center;
  font-size:2.2rem;
}

.table.print td .h6 {
  display: block;
  margin-bottom: 0.5rem;
  font-size:2.2rem;
}

</style>

<body>
    <div class="container-fluid">
      <div class="container-fluid">
          <div class="row m-0 mt-2 mb-2">
              <ul class="list-group list-group-horizontal">
                  <a href="#" class="list-menu" data-menu="order"><li class="list-group-item">주문보기</li></a>
                  <a href="#" class="list-menu" data-menu="view"><li class="list-group-item">메뉴보기</li></a>
                  <a href="#" class="list-menu" data-menu="new"><li class="list-group-item">메뉴생성</li></a>
                  <a href="#" class="option-excel"><li class="list-group-item">옵션다운로드</li></a>
              </ul>
          </div>
          <div class="form-row">
            <div class="col">
              <select name="year" class="form-control form-control-lg">
                <option value="2020">2020</option>
              </select>
            </div>
            <div class="col">
            <select name="month" class="form-control form-control-lg">
              <?php
              for ($i = 1; $i < 13; $i++) {
                if ($i == date('n')) echo '<option selected="selected" value="'.$i.'">'.$i.'</option>';
                else echo '<option value="'.$i.'">'.$i.'</option>';
              }
              ?>
              </select>
            </div>
            <div class="col-10">
            </div>
          </div>

          <div class="row m-0 mt-3 mb-2">
              <div class="wrap-chart"></div>
          </div>
      </div>
    </div>
</body>

<script>
  var dataNew = null;

  function clickMenu(e) {
    var menu = $(e.currentTarget).data('menu');
    var year = $('select[name=year]').val();
    var month = $('select[name=month]').val();

    if (!menu) return false;

    $.ajax({
        type: "POST",
        url: '../api/hanki.php',
        dataType : 'json',
        cache: false,
        timeout: 10000,
        data: {
            menu: menu,
            year: year,
            month: month
        },
        success: function (result, textStatus) {
            console.log(result);
            console.log(textStatus);
            window['render' + menu](result);
            dataNew = result;
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

  function renderorder(data) {
    if (!data) return false;

    var html = '';
    html += '<table class="table">';
    html += '<thead><tr><th scope="col">조리일</th><th scope="col">주문내역</th><th scope="col"></th></tr></thead>';
    html += '<tbody>';

    for (var i = 0; i < data.length; i++) {
      var contents = [];
      contents.push(data[i]['contents'][0]);
      contents.push(data[i]['contents'][3]);
      contents.push(data[i]['contents'][13]);

      html += '<tr data-id="' + data[i]['id'] + '">';
      html += '<td><input type="text" value="' + data[i]['date'] + '" class="form-control-sm form-control inp-date"/></td>';
      html += '<td>' + contents.join(' / ') + '</td>';
      html += '<td><button class="btn btn-secondary btn-sm mr-1 btn-edit-order">수정</button></td>';
      html += '</tr>';
    }

    html += '</tbody>';
    html += '</table>';

    $('.wrap-chart').html(html);
  }

  function renderview(data) {
    renderCalendar(data);
  }

  function rendernew(data) {
    renderCalendar(data);
    var html = '';
    html += '<div class="mt-5">';
    html += '<button type="button" class="save-new btn btn-primary btn-lg btn-block">저장</button>';
    html += '</div>';

    $('.wrap-chart').append(html);
  }

  function renderCalendar(data) {
    if (!data) return false;

    var html = '';
    html += '<table class="table print">';
    html += '<thead><tr><th scope="col">화</th><th scope="col">수</th><th scope="col">목</th><th scope="col">금</th><th scope="col"><span class="text-primary">토</span></th></tr></thead>';
    html += '<tbody>';

    var keys = Object.keys(data);

    while (keys.length > 0) {
      html += '<tr>';

      for (var i = 0; i < 7; i++) {
        var date = keys[0 ];

        if (!date) {
          keys.shift();
          html += '<td></td>';
          continue;
        }

        var dash = date.replace(/(\d{4})(\d{2})(\d{2})/g, '$1-$2-$3');
        var dayOfWeek = new Date(dash).getDay();

        if (dayOfWeek != i) {
          if (i == 0 || i == 1) {
            continue;
          }

          html += '<td></td>';
        } else {
          // 일, 월 제외
          if (i == 0 || i == 1) {
            keys.shift();
            continue;
          }

          // 메뉴
          var menuHtml = '';

          if (data[date] && data[date] != 0) {
            for (var j = 0; j < data[date].length; j++) {
              var txt = data[date][j];

              if (i == 4 && j > 5) {
                txt = '<b>' + txt + '</b>';
              }

              if (j < 3) {
                txt = '<span class="text-secondary">' + txt + '</span>';
              }

              menuHtml += '<p>' + txt + '</p>';

              // if (j == 2) {
              //   menuHtml += '<div style="border-top:3px dashed #4F9EC4"></div>';
              // } else if (i == 4 && j == 5) {
              //   menuHtml += '<div style="border-top:3px dashed #FF756D"></div>';
              // }
            }
          }

          var month = date.substr(4, 2).replace(/^0+/, '');
          var day = date.substr(6, 2).replace(/^0+/, '');

          day = month + '/' + day;

          if (i == 0) {
            day = '<span class="text-danger h6">' + day + '</span>';
          } else if (i == 6) {
            day = '<span class="text-primary h6">' + day + '</span>';
          } else {
            day = '<span class="h6">' + day + '</span>';
          }

          html += '<td>';
          html += day;
          html += '<div>';
          html += menuHtml;
          html += '<div>';
          html += '</td>';
          keys.shift();
        }
      }

      html += '</tr>';
    }

    html += '</tbody>';
    html += '</table>';

    $('.wrap-chart').html(html);
  }

  function saveNew() {
    if (!dataNew) return false;

    $.ajax({
        type: "POST",
        url: '../api/hanki.php',
        dataType : 'json',
        cache: false,
        timeout: 10000,
        data: {
            menu: 'saveNew',
            data: dataNew
        },
        success: function (result, textStatus) {
            console.log(result);
            console.log(textStatus);

            if (result['result'] == true) {
              alert('저장했습니다.');
            } else {
              alert('저장에 실패했습니다.');
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
  }

  function optionExcel() {
    window.open('hanki.php?excel=1', '_blank');
  }

  function editOrder(e) {
    if (confirm("수정하시겠습니까?")) {
        var row = $(e.currentTarget).closest('tr').eq(0);

        var data = {
            id: $(row).data('id'),
            date: $(row).find('.inp-date').val(),
        };

        // console.log(data);

        $.ajax({
            type: 'POST',
            url: '../api/hanki.php',
            dataType : 'json',
            timeout: 5000,
            data: {
                menu: 'editorder',
                data: data
            },
            success: function (result, textStatus) {
                console.log(result);
                console.log(textStatus);

                if (result['result'] == true) {
                    location.reload();
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
    }
}

  $(document).ready(function() {
    $(document).on('click', '.list-menu', clickMenu);
    $(document).on('click', '.save-new', saveNew);
    $(document).on('click', '.option-excel', optionExcel);
    $(document).on('click', '.btn-edit-order', editOrder);
  });
</script>

</html>