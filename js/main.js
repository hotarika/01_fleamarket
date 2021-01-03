$(function () {
   /* *********************************
    * 共通化
    * ********************************* */
   // フッターを最下部に固定
   var $ftr = $("#js-ftr");
   if (window.innerHeight > $ftr.offset().top + $ftr.outerHeight()) {
      $ftr.attr({
         style: "position:fixed; top:" +
            (window.innerHeight - $ftr.outerHeight()) +
            "px;",
      });
   }

   /* *********************************
    * メッセージを表示
    * ********************************* */
   var $jsShowMsg = $("#js-show-msg");
   var msg = $jsShowMsg.text();
   if (msg.replace(/^[\s　]+|[\s　]+$/g, "").length) {
      $jsShowMsg.slideToggle("slow");
      setTimeout(function () {
         $jsShowMsg.slideToggle("slow");
      }, 5000);
   }

   /* *********************************
    * 画像ライブプレビュー
    * ********************************* */
   var $dropArea = $(".js-dropArea");
   var $fileInput = $(".js-inputFile");
   // dragoverの挙動
   $dropArea.on("dragover", function (e) {
      e.stopPropagation();
      e.preventDefault();
      $(this).css("border", "3px #ccc dashed");
   });
   // dragleaveの挙動
   $dropArea.on("dragleave", function (e) {
      e.stopPropagation();
      e.preventDefault();
      $(this).css("border", "none");
   });
   // ライブプレビューの動作
   $fileInput.on("change", function (e) {
      $dropArea.css("border", "none");
      var file = this.files[0];
      var $img = $(this).siblings(".js-prevImg");
      var fileReader = new FileReader();

      fileReader.readAsDataURL(file);
      fileReader.onload = function (event) {
         $img.attr("src", event.target.result).show();
      };
   });

   /* *********************************
    * 文字カウント
    * ********************************* */
   $("#js-detail").on("keyup", function () {
      $("#js-counter").php($(this).val().length);
   });

   /* *********************************
    * messageスクロール
    * ********************************* */
   $(function () {
      //scrollHeightは要素のスクロールビューの高さを取得するもの
      //(多分)js-scroll-bottomの配列0の高さを取得して、それとscrollTopに設定している
      $("#js-scroll-bottom").animate({
            scrollTop: $("#js-scroll-bottom")[0].scrollHeight
         },
         "fast"
      );
      console.log($("#js-scroll-bottom")[0].scrollHeight);
   });

   /* *********************************
    * 画面切り替え
    * ********************************* */
   $(".js-switchImg").on("click", function (e) {
      $("#js-mainImg").attr("src", $(this).attr("src"));
   });

   /* *********************************
    * お気に入り登録・削除
    * ********************************* */
   // お気に入り登録・削除
   var $like, likeProductId;
   $like = $(".pm-prodDetail__like") || null; //nullというのはnull値という値で、「変数の中身は空ですよ」と明示するためにつかう値
   likeProductId = $like.data("productid") || null;

   // 数値の0はfalseと判定されてしまう。product_idが0の場合もありえるので、0もtrueとする場合にはundefinedとnullを判定する
   if (likeProductId !== undefined && likeProductId !== null) {
      $like.on("click", function () {
         var $this = $(this);
         $.ajax({
               type: "POST",
               url: "f-ajaxLike.php",
               data: {
                  productId: likeProductId
               },
            })
            .done(function (data) {
               console.log("Ajax Success");
               $this.toggleClass("is-active");
            })
            .fail(function (msg) {
               console.log("Ajax Error");
            });
      });
   }
});
