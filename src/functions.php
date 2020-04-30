<?php
namespace mikecai\fancyphoto;

function render_css(){
	$css = '<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.css" />'."\n";
	echo $css;

	$css ='
		<style type="text/css">
			.fancybox_div{
			    float: left;
			    position: relative;
			    vertical-align: top;
			    width: 210px;
			}

			.fancybox_a{
			    position: relative;
			    vertical-align: top;
			}

			.fancybox_remove_btn{
			    z-index: 20;
			    color: #fff;
			    background-color: #F3565D;
			    border-color: #f13e46;
			    position: absolute;
			    right: 10px;
			}

			.fancybox_img{
			    border: black 3px solid;
			    margin-bottom: 5px;
			    margin-right: 10px;
			    height: 20vh;
			}
		</style>
	'; 
	echo $css;
}


function render_footer_js(){
	$js = '<script src="https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.js"></script>'."\n";
	echo $js;
	$js = '<script src="https://malsup.github.io/jquery.form.js"></script>'."\n";
	echo $js;
}

function renader_body(){
	$body = '
	    <button class="btn btn-success" data-toggle="modal" data-target="#Upload_Modal">上傳圖片</button>
	    <div class="modal fade" id="Upload_Modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	      <div class="modal-dialog" role="document">
	        <div class="modal-content">
	          <div class="modal-header">
	            <h2 class="modal-title" id="exampleModalLabel">請選擇上傳圖片</h2>
	            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
	              <span aria-hidden="true">&times;</span>
	            </button>
	          </div>
	          <div class="modal-body">
	            <label class="btn btn-default">選取圖片 
	                <form action="#" id="uploadForm" name="frmupload" method="post" enctype="multipart/form-data">
	                    <input type="file" name="fancy_photo"  id="fancy_photo" accept="image/*" onchange="readURL(this);" hidden>
	                </form>
	            </label>
	            <br>


	            <div class="bar"  id="progressDivId" style="display: none;margin-top: 20px;"></div >

	            <div id="status"></div>

	            <img  id="target_image" src="" style="max-width: 100%;margin-top: 20px;">
	          </div>
	          <div class="modal-footer">
	            <button id="submitButton" type="button" class="btn btn-primary">上傳</button>
	            <button type="button" id="modal-close" class="btn btn-secondary" data-dismiss="modal">Close</button>
	          </div>
	        </div>
	      </div>
	    </div>
	    <br>
	    <hr>
	    <div id="fancy_photo_gallery"></div>
	'."\n";
	echo $body;
}

function render_ajax($contorller_name,$contorller_id,$ajax_upload_image_api="",$ajax_get_image_api="",$ajax_remove_image_api=""){
	$ajax_string = '
	<script>
		$(function(){
		    $("#submitButton").click(function () {
		        $("#progressDivId").css({
		            "display":"block"
		        });
		        var image_form_data = new FormData();

		        image_form_data.append("file", $("#fancy_photo")[0].files[0]);
		        image_form_data.append("controller_name", "'.$contorller_name.'");
		        image_form_data.append("controller_id", '.$contorller_id.');
		        // console.log(image_form_data);

		        $.ajax({
		          xhr: function() {
		            var xhr = new window.XMLHttpRequest();
		            xhr.upload.addEventListener("progress", function(evt) {

		              $(".bar").css("color", "wheat");
		              $(".bar").css("background", "green");

		              var percentComplete = evt.loaded / evt.total;
		              percentComplete = parseInt(percentComplete * 100);

		              $(".bar").html(percentComplete + " %");
		              $(".bar").css("width", percentComplete + "%");
		              console.log(percentComplete);
		            },false);
		            return xhr;
		          },
		          url: "'.$ajax_upload_image_api.'",
		          type: "POST",
		          data: image_form_data,
		          processData: false,
		          contentType: false,
		          success: function(result) {
		            $("#target_image").attr("src","");
		            $("#progressDivId").css({"display":"none"});
		            $("#fancy_photo").val("");
		            $("#modal-close").trigger( "click" );
		            load_fancy_photo();
		            //reload image
		            alert("上傳成功");
		          }
		        });
		    });

		    //預設載入圖片
		    load_fancy_photo();
		});

		function readURL(input) {
		  if (input.files && input.files[0]) {
		    var reader = new FileReader();
		    reader.onload = function (e) {
		      $("#target_image")
		        .attr("src", e.target.result)
		        .width(200);
		    };
		    reader.readAsDataURL(input.files[0]);
		  }
		}

		function load_fancy_photo(){

		    var image_form_data = new FormData();


		    $.ajax({
		        url: "'.$ajax_get_image_api.'",
		        type: "POST",
		        data: {
		        "controller_name": "'.$contorller_name.'",
		        "controller_id": '.$contorller_id.'
		        },
		    })
		    .done(function(data) {
		        $("#fancy_photo_gallery").html(data);
		        $(".fancybox_remove_btn").on("click", function() {

		            var r=confirm("確定要刪除此張圖片嗎?")

		            if (r) {
		                var id = $(this).attr("data-id");

		                $.ajax({
		                    url: "'.$ajax_remove_image_api.'",
		                    type: "POST",
		                    data: {"id": id},
		                })
		                .always(function() {
		                    console.log("complete");
		                    load_fancy_photo();
		                });
		            }
		            
		        });
		    });
		}
	</script>
	'."\n";

	echo $ajax_string;
}
