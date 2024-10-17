<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8" />
  <title>週間スケジュール</title>
  <style>
    .week-schedule {
      display: flex;
      justify-content: space-between;
      margin-top: 50px;
    }

    .day {
      width: 14%;
      border: 1px solid #000;
      min-height: 600px;
      position: relative;
    }

    .activity {
      padding: 10px;
      margin: 5px;
      color: #fff;
      font-size: 14px;
      text-align: center;
      border-radius: 5px;
      cursor: move;
    }

    .outside-activities {
      display: flex;
      flex-wrap: wrap;
      margin-top: 20px;
    }

    #activity-code {
      box-shadow: 0px 0px 3px #555;
      border-radius: 5px;
      position: fixed;
      bottom: 10px;
      right: 10px;
      border: 1px solid #555;
      padding: 10px;
      background-color: #f1f1f1;
    }

    #reset-button,
    #set-button,
    #copy-button {
      margin-left: 10px;
      padding: 5px 10px;
      background-color: #ffcccc;
      border: 1px solid #000;
      cursor: pointer;
    }

    #set-button {
      background-color: #ccffcc;
    }

    #copy-button {
      background-color: #ccccff;
    }

    #code-output {
      width: 200px;
    }
  </style>
</head>

<body>

  <?php
  // DB接続関数
  function db_access()
  {
    $user = 'kinokonosato';
    $pass = 'P00027511wy3';
    $dbnm = 'kinokonosato';
    $host = 'localhost';
    $connect = "mysql:host={$host};dbname={$dbnm}";

    try {
      $pdo = new PDO($connect, $user, $pass, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
      $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (Exception $e) {
      // 運用環境ではエラーメッセージを直接出力しない
      error_log($e->getMessage());
      echo "<p>DB接続エラーが発生しました。</p>";
      exit();
    }

    return $pdo;
  }

  // アクティビティコードをDBから取得する関数
  function get_activitycodes()
  {
    $pdo = db_access();
    try {
      // クエリ実行
      $stmt = $pdo->query("SELECT * FROM groupware_block ORDER BY updatetime DESC");
      return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
      // エラーメッセージをログに記録
      error_log($e->getMessage());
      echo "<p>アクティビティコードの取得時にエラーが発生しました。</p>";
    }
    return [];
  }


  // アクティビティの配列
  $activities = [
    ["id" => "A01", "name" => "植菌準備", "color" => "#c79696"],
    ["id" => "A02", "name" => "収穫", "color" => "#85cf85"],
    ["id" => "A03", "name" => "水源整備", "color" => "#8cadd3"],
    ["id" => "A04", "name" => "袋詰め", "color" => "#c79696"],
    ["id" => "A05", "name" => "植菌", "color" => "#c79696"],
    ["id" => "A06", "name" => "点火・窯出し", "color" => "#c79696"],
    ["id" => "A07", "name" => "栄養体準備", "color" => "#c79696"],
    ["id" => "A08", "name" => "菌床洗い", "color" => "#85cf85"],
    ["id" => "A09", "name" => "菌床移動", "color" => "#85cf85"],
    ["id" => "A10", "name" => "除袋", "color" => "#85cf85"],
    ["id" => "A11", "name" => "浸水出す", "color" => "#8cadd3"],
    ["id" => "A12", "name" => "浸水入れる", "color" => "#8cadd3"],
    ["id" => "A13", "name" => "菌床廃棄", "color" => "#97703e"],
    ["id" => "A14", "name" => "商品準備", "color" => "#e0d860"],
    ["id" => "A15", "name" => "ﾊﾟｯｸ詰め", "color" => "#e0d860"],
    ["id" => "A16", "name" => "出荷", "color" => "#e0d860"],
  ];

  ?>

  <!-- アクティビティリストの動的生成 -->
  <div class="outside-activities">
    <?php foreach ($activities as $activity): ?>
      <div
        class="activity"
        style="background-color: <?= $activity['color']; ?>"
        draggable="true"
        id="<?= $activity['id']; ?>">
        <?= $activity['name']; ?>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- 曜日のドロップエリア -->
  <div class="week-schedule">
    <?php
    $days = ["月曜日" => "MON", "火曜日" => "TUE", "水曜日" => "WED", "木曜日" => "THU", "金曜日" => "FRI", "土曜日" => "SAT", "日曜日" => "SUN"];
    foreach ($days as $day_name => $day_code): ?>
      <div
        class="day"
        data-day="<?= $day_code; ?>"
        ondrop="drop(event)"
        ondragover="allowDrop(event)">
        <h3><?= $day_name; ?></h3>
        <div class="total">合計: 0</div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- アクティビティコードセクション -->
  <div id="activity-code">
    アクティビティコード:
    <input type="text" id="code-output" />
    <button id="reset-button">リセット</button>
    <button id="set-button">セット</button>
    <button id="copy-button">コピー</button>
    <button id="save-db-button">DBへ保存</button>
  </div>

  <!-- DBから取得したアクティビティコードの表示 -->
  <div id="db-activitycodes">
    <h3>保存されたアクティビティコード</h3>
    <ul id="activity-list">
      <?php
      $saved_codes = get_activitycodes();
      foreach ($saved_codes as $code) {
        echo "<li>{$code['updatetime']}: {$code['activitycode']} - 作者: {$code['author']}</li>";
      }
      ?>
    </ul>
  </div>

  <script>
    let draggedElement = null;
    let sourceElement = null;

    function allowDrop(event) {
      event.preventDefault();
    }

    function drag(event) {
      const tempDiv = document.createElement("div");
      tempDiv.innerHTML = event.target.outerHTML;
      draggedElement = tempDiv.firstChild;
      sourceElement = event.target.closest(".activity");
    }

    function drop(event) {
      event.preventDefault();
      const dayElement = event.target.closest(".day");
      const targetElement = event.target.closest(".activity");

      if (dayElement) {
        const activityClone = draggedElement.cloneNode(true);
        activityClone.removeAttribute("id");
        addDragEvents(activityClone);

        if (targetElement) {
          const parent = targetElement.parentNode;
          const draggedNext = activityClone.nextElementSibling === targetElement;

          parent.insertBefore(
            activityClone,
            draggedNext ? targetElement.nextElementSibling : targetElement
          );
        } else {
          dayElement.appendChild(activityClone);
        }

        if (sourceElement && sourceElement.closest(".day")) {
          sourceElement.remove();
        }

        updateActivityCode();
        updateTotal(dayElement);
        updateTotal(sourceElement.closest(".day"));
      }
    }

    function addDragEvents(activity) {
      activity.addEventListener("dragstart", drag);
      activity.addEventListener("dblclick", () => {
        const dayElement = activity.closest(".day");
        activity.remove();
        updateActivityCode();
        updateTotal(dayElement);
      });
      activity.addEventListener("contextmenu", (event) => {
        event.preventDefault();
        if (event.shiftKey && activity.previousElementSibling) {
          activity.parentNode.insertBefore(
            activity,
            activity.previousElementSibling
          );
        } else if (!event.shiftKey && activity.nextElementSibling) {
          activity.parentNode.insertBefore(
            activity.nextElementSibling,
            activity
          );
        }
        updateActivityCode();
        updateTotal(activity.closest(".day"));
      });
    }

    document.querySelectorAll(".activity").forEach((item) => {
      addDragEvents(item);
    });

    function updateActivityCode() {
      let code = "";
      document.querySelectorAll(".day").forEach((day) => {
        const dayCode = day.getAttribute("data-day");
        day.querySelectorAll(".activity").forEach((activity) => {
          const activityText = activity.textContent.trim();
          switch (activityText) {
            case "植菌準備":
              code += dayCode + "A01";
              break;
            case "収穫":
              code += dayCode + "A02";
              break;
            case "水源整備":
              code += dayCode + "A03";
              break;
            case "袋詰め":
              code += dayCode + "A04";
              break;
            case "植菌":
              code += dayCode + "A05";
              break;
            case "点火・窯出し":
              code += dayCode + "A06";
              break;
            case "栄養体準備":
              code += dayCode + "A07";
              break;
            case "菌床洗い":
              code += dayCode + "A08";
              break;
            case "菌床移動":
              code += dayCode + "A09";
              break;
            case "除袋":
              code += dayCode + "A10";
              break;
            case "浸水出す":
              code += dayCode + "A11";
              break;
            case "浸水入れる":
              code += dayCode + "A12";
              break;
            case "菌床廃棄":
              code += dayCode + "A13";
              break;
            case "商品準備":
              code += dayCode + "A14";
              break;
            case "ﾊﾟｯｸ詰め":
              code += dayCode + "A15";
              break;
            case "出荷":
              code += dayCode + "A16";
              break;
          }
        });
      });
      document.getElementById("code-output").value = code;
    }

    function updateTotal(dayElement) {
      const activities = dayElement.querySelectorAll(".activity");
      const total = activities.length * 0.5;
      const totalElement = dayElement.querySelector(".total");
      totalElement.textContent = `合計: ${total}`;
    }

    function setActivityFromCode(code) {
      document.querySelectorAll(".day").forEach((day) => {
        const activities = day.querySelectorAll(".activity");
        activities.forEach((activity) => activity.remove());
        updateTotal(day);
      });

      for (let i = 0; i < code.length; i += 6) {
        const dayCode = code.substring(i, i + 3);
        const activityCode = code.substring(i + 3, i + 6);

        const dayElement = document.querySelector(`.day[data-day="${dayCode}"]`);
        if (!dayElement) continue;

        const newActivity = document.createElement("div");
        newActivity.classList.add("activity");
        newActivity.setAttribute("draggable", "true");
        addDragEvents(newActivity);

        switch (activityCode) {
          case "A01":
            newActivity.textContent = "植菌準備";
            newActivity.style.backgroundColor = "#c79696";
            break;
          case "A02":
            newActivity.textContent = "収穫";
            newActivity.style.backgroundColor = "#85cf85";
            break;
          case "A03":
            newActivity.textContent = "水源整備";
            newActivity.style.backgroundColor = "#8cadd3";
            break;
          case "A04":
            newActivity.textContent = "袋詰め";
            newActivity.style.backgroundColor = "#c79696";
            break;
          case "A05":
            newActivity.textContent = "植菌";
            newActivity.style.backgroundColor = "#c79696";
            break;
          case "A06":
            newActivity.textContent = "点火・窯出し";
            newActivity.style.backgroundColor = "#c79696";
            break;
          case "A07":
            newActivity.textContent = "栄養体準備";
            newActivity.style.backgroundColor = "#c79696";
            break;
          case "A08":
            newActivity.textContent = "菌床洗い";
            newActivity.style.backgroundColor = "#85cf85";
            break;
          case "A09":
            newActivity.textContent = "菌床移動";
            newActivity.style.backgroundColor = "#85cf85";
            break;
          case "A10":
            newActivity.textContent = "除袋";
            newActivity.style.backgroundColor = "#85cf85";
            break;
          case "A11":
            newActivity.textContent = "浸水出す";
            newActivity.style.backgroundColor = "#8cadd3";
            break;
          case "A12":
            newActivity.textContent = "浸水入れる";
            newActivity.style.backgroundColor = "#8cadd3";
            break;
          case "A13":
            newActivity.textContent = "菌床廃棄";
            newActivity.style.backgroundColor = "#97703e";
            break;
          case "A14":
            newActivity.textContent = "商品準備";
            newActivity.style.backgroundColor = "#e0d860";
            break;
          case "A15":
            newActivity.textContent = "ﾊﾟｯｸ詰め";
            newActivity.style.backgroundColor = "#e0d860";
            break;
          case "A16":
            newActivity.textContent = "出荷";
            newActivity.style.backgroundColor = "#e0d860";
            break;
        }

        dayElement.appendChild(newActivity);
        updateTotal(dayElement);
      }

      updateActivityCode();
    }

    document.getElementById("set-button").addEventListener("click", () => {
      const code = document.getElementById("code-output").value;
      setActivityFromCode(code);
    });

    document.getElementById("reset-button").addEventListener("click", () => {
      document.getElementById("code-output").value = "";
      document.querySelectorAll(".day").forEach((day) => {
        day.querySelectorAll(".activity").forEach((activity) => activity.remove());
        updateTotal(day);
      });
    });

    document.getElementById("copy-button").addEventListener("click", () => {
      const codeOutput = document.getElementById("code-output");
      codeOutput.select();
      document.execCommand("copy");
    });

    document.getElementById('save-db-button').addEventListener('click', function() {
      // 入力フィールドの値を取得
      var code = document.getElementById('code-output').value;

      // AjaxリクエストでPHPにデータを送信
      var xhr = new XMLHttpRequest();
      xhr.open('POST', 'save_activitycode.php', true);
      xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

      xhr.onload = function() {
        if (xhr.status === 200) {
          alert('アクティビティコードが保存されました');
        } else {
          alert('保存に失敗しました');
        }
      };

      // 入力されたコードをサーバーに送信
      xhr.send('code=' + encodeURIComponent(code));
    });
  </script>

</body>

</html>