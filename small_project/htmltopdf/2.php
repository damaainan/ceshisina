<?php
header("Content-type:text/html; Charset=utf-8");
include("pdf/ConverterExtra.php");
$converter = new ConverterExtra();
$html=<<<EOF
<div class="v-left-sild-1">
				<dl>
					<dt class="v-relative">
						<video id="video"  class="v-left-sild-img" title="" src="" style="background:rgba(0,0,0,0.4);z-index:999;" poster="" preload></video>

					</dt>
					<dd class="newSpeedPos">
						<p id="speed"></p>
					</dd>
					<dd class="newVideoDd">
						<p class="videoContorl" id="play"></p>
						<p class="videoWidth"></p>
						<p class="videotime">
							<span id="fen">00</span>
							<span>:</span>
							<span id="startTime">00</span>
							<span>/</span>
							<span id="Ffen">00</span>
							<span>:</span>
							<span id="time">00</span>
						</p>
						
					</dd>
					<dd>
						<div class="videologo">
							<span class="v-left-sild-bofang">
							    <span class="v-left-sild-four"></span>
						    </span>
						    <span class="newVideoLIne">|</span>
						    <span class="v-left-sild-bofang" id="zan" data-url="" data-videoId="">
						        <span class="v-left-sild-xin" title=""></span>
							</span>
							<span class="v-left-sild-bofang">
						      	 <a href="#loading">
									<span class="v-left-sild-lun"></span>
								 </a>
							</span>
							 <span class="newVideoLIne">|</span>
						  <p style="display:none"><img data-original="" id="baiduShareImg" src="__PUBLIC__/images/11.png"></p>
						   <span class="v-left-sild-baidu">

						 	<div class="bdsharebuttonbox" style="display:table-cell;vertical-align: middle;">
						 		<a href="#" class="bds_more" data-cmd="more"></a>
						 		<a href="#" class="bds_qzone" data-cmd="qzone"></a>
						 		<a href="#" class="bds_tsina" data-cmd="tsina"></a>
						 		<a href="#" class="bds_weixin" data-cmd="weixin"></a>
						 	</div>
							</span>
							<span class="newVideoAtt">+关注</span>

							<span class="newVideoNike">
								
							</span>
							<img class="newVideoArr" src="__PUBLIC__/images/nick.png" alt="" data-original="">
						</div>

					</dd>
				</dl>
			</div>
EOF;
$str=$converter->parseString('<h1 id="md">Heading</h1>');
// $str=$converter->parseString($html);
echo $str;
// Returns: # Heading