<?php
/**
 * @description Based on action value specific condtion will be executed
 * @author Rakesh Salunke <rakesh.salunke@pinstorm.com>
 * @modify 14 Dec 2016
 */
	if(isset($_POST["action"]) && !empty($_POST["action"])):
		extract($_POST);

		switch($action):

			case 'get-reco':
				//update cfbtoken session value.
				if(isset($cfbToken) && !empty($cfbToken))
					$_SESSION['cfbToken'] = $cfbToken;

				$_SESSION['selectedDimension'] = array();
				$filter_param = array();
				if(isset($_POST["filterdata"]) && !empty($_POST["filterdata"])):
					foreach($_POST["filterdata"] as $filterKey => $filterVal):
						if(isset($filterVal["value"]) && !empty($filterVal["value"])):
							$arraykey = trim($filterVal["name"],"[]");
							$arraykey = str_replace("filter_", "", $arraykey);
							//if filter has multiple value store it into array else single variable.
							if(strpos($filterVal["name"], '[]'))
								$filter_param[$arraykey][]=$filterVal["value"];
							else
								$filter_param[$arraykey]=$filterVal["value"];
						endif;
					endforeach;
				endif;

				$_SESSION["startdate"] = (isset($startdate) && !empty($startdate)) ? $startdate : $_SESSION["startdate"];
				$_SESSION["enddate"] = (isset($enddate) && !empty($enddate)) ? $enddate : $_SESSION["enddate"];
				$filter_param["limit"] = (isset($limit) && !empty($limit)) ? $limit : INT_MENTION_PER_PAGE;

				$filter_param["startdate"] = $_SESSION["startdate"];
				$filter_param["enddate"] = $_SESSION["enddate"];


				//today's date start from 15:30:01 of last treding date.
				//get last treding date.
				$current_date =date_create(date("d-m-Y",time()));
				$start_date =date_create(date("d-m-Y",strtotime($filter_param["startdate"])));
				$diff = date_diff($current_date,$start_date);
				$startDateCount = $diff->d + 1;
				$get_start_date = $confab->getPreviousDate($startDateCount);


				//Change startdate value
				$filter_param["startdate"] = (isset($get_start_date) && !empty($get_start_date)) ? date("d-m-Y",$get_start_date) : $_SESSION["startdate"];

				// exit;
				$_SESSION['filter_param'] = $filter_param;
				$records = $confab->get_reco($filter_param);

				if(isset($records["payload"]) && count($records["payload"])):
					$output["code"] = 200;
					$output["payload"] = $records["payload"];
					$output["calc_found_rows"] = $records["calc_found_rows"];
				else:
					$output["code"] = 204;
					$output["msg"] = "No Record Found";

				endif;
				// echo json_encode($output,JSON_UNESCAPED_UNICODE);
				echo json_encode($output,true);

			break;


			case 'get-alerts':


				if($startdate=="07-08-2017" && $enddate=="07-08-2017"):
					echo file_get_contents(__ROOT__."/account/nseit/controllers/alert.json");
				else:

					$param = array();
					$startdate = (isset($startdate) && !empty($startdate)) ? $startdate : START_DATE;
					$enddate = (isset($enddate) && !empty($enddate)) ? $enddate: END_DATE;
					$_SESSION["startdate"] = $startdate;
					$_SESSION["enddate"] = $enddate;

					$param['startdate'] = $startdate;
					$param['enddate'] = $enddate;

					if(isset($company_id) && !empty($company_id))
						$param["x0"]=$company_id;

					$param["min_stock_price"] = MIN_STOCK_PRICE_IN_ALERT;

					$variation_price = $confab->get_variations_with_prev_price($param);

					// $confab->printdata($variation_price);
					// exit;


					$alerts = array();

					foreach($variation_price as $variationKey => $variations):
						$symbol = $variations["company_symbol"];
						$company_name = $variations["dimension_attribute"];
						if(!isset($alerts[$symbol])):
							$alerts[$symbol] = array(
													"company_id" => $variations["company_id"],
													"dimension_attribute" => ucwords(strtolower($variations["dimension_attribute"])),
													"company_symbol" => $variations["company_symbol"],
													"highestVariation" => number_format($variations["variation_with_prev_price"],2),
													);
						endif;
						$alerts[$symbol]["variations"][$variations["cmp_time"]] = array(
												"id" => $variations["id"],
												"cmp" => number_format($variations["cmp"],2),
												"cmp_time" => $variations["cmp_time"],
												"cmp_time_display" => date("d M 'y h:i a",$variations["cmp_time"]),
												"insert_time" =>$variations["insert_time"],
												"insert_time_display" => date("d-m-Y H:i:s",$variations["insert_time"]),
												"status" => $variations["status"],
												"start_close_price_flag" => $variations["start_close_price_flag"],
												"low_high_price_flag" => $variations["low_high_price_flag"],
												"stock_volume" => $variations["stock_volume"],
												"variation_with_prev_price" => number_format($variations["variation_with_prev_price"],2),
												"variation_with_prev_close" => number_format($variations["variation_with_prev_close"],2)
												);

						if(!isset($alerts[$symbol]["today_high"]) || empty($alerts[$symbol]["today_high"])):
							//high
							$mysqli->where("x0_id",$variations["company_id"]);
							$mysqli->where("cmp_time",array(strtotime($startdate),strtotime($enddate ." 23:59:59")),"BETWEEN");
							$getTodayHigh = $mysqli->getOne(STOCK_CMP,"max(cmp) as today_high");
							$alerts[$symbol]["today_high"] = (count($getTodayHigh)) ? number_format($getTodayHigh["today_high"],2) : '-';
							//Get todays low
							$mysqli->where("x0_id",$variations["company_id"]);
							$mysqli->where("cmp_time",array(strtotime($startdate),strtotime($enddate ." 23:59:59")),"BETWEEN");
							$getTodayLow = $mysqli->getOne(STOCK_CMP,"min(cmp) as today_low");
							$alerts[$symbol]["today_low"] = (count($getTodayLow)) ? number_format($getTodayLow["today_low"],2) : '-';
							//Get todays opeping price
							$mysqli->where("x0_id",$variations["company_id"]);
							$mysqli->where("cmp_time",array(strtotime($startdate),strtotime($enddate ." 23:59:59")),"BETWEEN");
							$mysqli->orderBy("cmp_time","ASC");
							$getTodayOpen = $mysqli->getOne(STOCK_CMP,"cmp");
							$alerts[$symbol]["today_open"] = (count($getTodayOpen)) ? number_format($getTodayOpen["cmp"],2) : '-';

							//Yesterday Closing price
							$date1 =date_create(date("Y-m-d",time()));
							// $date2 =date_create(date("Y-m-d",strtotime('-1 day',strtotime($filter_param["startdate"]))));
							$date2 =date_create(date("Y-m-d",strtotime($startdate)));
							$diff = date_diff($date1,$date2);
							$startDateCount = $diff->d + 1;
							$get_start_date = $confab->getPreviousDate($startDateCount);
							$yesterdatCloseFilter = array(
														"startdate" => date("d-m-Y",$get_start_date),
														"enddate" => date("d-m-Y",$get_start_date),
														"start_close_price_flag"=>2,
														"company_id"=>$variations["company_id"]
														);

							// $confab->printdata($yesterdatCloseFilter);
							$yesterdayClose = '-';
							$yesterday = date("d-m-Y",$get_start_date);
							$getYesterdayClosing = $confab->get_stock_price($yesterdatCloseFilter);
							$yesterdayClose = number_format(array_values($getYesterdayClosing[$company_name]['cmp'][$yesterday])[0],2);
							if(isset($yesterdayClose))
								$alerts[$symbol]["yesterday_close"] = $yesterdayClose;

							//Get Current Market price
							$mysqli->where("x0_id",$variations["company_id"]);
							$mysqli->orderBy("cmp_time","DESC");
							$currentMP = $mysqli->getOne(STOCK_CMP);
							// echo $mysqli->getLastQuery();
							// print_r($currentMP);

							$cmp_variation = "-";
							if($yesterdayClose!="-")
								$cmp_variation = (($currentMP['cmp'] - array_values($getYesterdayClosing[$company_name]['cmp'][$yesterday])[0]) / array_values($getYesterdayClosing[$company_name]['cmp'][$yesterday])[0]) * 100;

							$alerts[$symbol]["cmp_variation"] = number_format($cmp_variation,2);

							$alerts[$symbol]["cmp"] = number_format($currentMP['cmp'],2);



							//Get mentions between start and end date.
							//Get Company News - Only News Platform means priority=1 mentions
							// $news_startAt = strtotime(date("d-m-Y H:i:s",strtotime("-".NEWS_PREQUEL." minutes",$variations["cmp_time"])));
							// $news_endAt = strtotime(date("d-m-Y H:i:s",strtotime("+".NEWS_SEQUEL." minutes",$variations["cmp_time"])));
							$mysqli->join(MENTIONS_DETAILS." md","m.id=md.m_id","INNER");
							$mysqli->where("m.post_time",array(strtotime($startdate),strtotime($enddate ." 23:59:59")),"BETWEEN");
							$mysqli->where("md.x0",$variations["company_id"]);
							$mysqli->where("m.priority",1);
							$mysqli->where("m.status",IGNORE_STATUS_CODE,"!=");

							// $mysqli->where("md.x4",array(PLATFORM_TYPE_SOCIAL_MEDIA,PLATFORM_TYPE_NEWS_ARTICLES),"IN");
							// $mysqli->where("m.title NOT LIKE 'RT @%'");
							$mysqli->groupBy("m.metaphone");
							$mysqli->orderBy("mention_count","DESC");
							$selectFields = "count(m.id) as mention_count, m.id, m.url, m.metaphone, m.title, m.description, m.post_time, m.author";
							$relatedNews = $mysqli->get(MENTIONS." m", 5 ,$selectFields);
							// if(isset($variations['company_id']) && $variations['company_id']==304)
							// 	echo $mysqli->getLastQuery();

							if(count($relatedNews)):
								foreach($relatedNews as $newsLoop => $newsData):

									if(isset($newsData["author"]) && !empty($newsData["author"])):
										$authorFilter = array("id"=>$newsData["author"]);
										$getAuthorInfo = $confab->getAuthorInfo($authorFilter);

									endif;


									$alerts[$symbol]["news"][$newsData['id']] = array(
															"mention_count" => $newsData["mention_count"],
															"mention_id" => $newsData["id"],
															"url" => $newsData["url"],
															"metaphone" => $newsData["metaphone"],
															"title" => $newsData["title"],
															"description" => $newsData["description"],
															"post_time" => $newsData["post_time"],
															"post_time_display" => date("d M,'y h:i a",$newsData["post_time"]),
															"author" => $newsData["author"],
															);

									if(isset($getAuthorInfo) && !empty($getAuthorInfo))
										$alerts[$symbol]["news"][$newsData['id']]["author_info"] = $getAuthorInfo;

								endforeach;
							endif;





							//Get latest Annoucement
							$ann_filter = array(
												"startdate" => date("d-m-Y",strtotime($startdate)),
												"enddate" => date("d-m-Y",strtotime($enddate ." 23:59:59")),
												"company_id" => $variations["company_id"]
												);
							$getAnnnoucement = $confab->get_company_announcement($ann_filter);
							// print_r($getAnnnoucement);

							if(isset($getAnnnoucement) && !empty($getAnnnoucement)):
								foreach($getAnnnoucement as $announcementKey => $announcement):
									$alerts[$symbol]["news"][$newsData['id']] = array(
																	"url" => $announcement["url"],
																	"title" => $announcement["title"],
																	"description" => $announcement["description"],
																	"post_time" => $announcement["post_time"],
																	"post_time_display" => date("d M,'y h:i a",$announcement["post_time"]),

																					);
								endforeach;
							endif;


							//Get Social Media News - Except news platforms

							// $news_startAt = strtotime(date("d-m-Y H:i:s",strtotime("-".TWEET_PREQUEL." minutes",$variations["cmp_time"])));
							// $news_endAt = strtotime(date("d-m-Y H:i:s",strtotime("+".TWEET_SEQUEL." minutes",$variations["cmp_time"])));

							$mysqli->join(MENTIONS_DETAILS." md","m.id=md.m_id","INNER");
							$mysqli->where("m.post_time",array(strtotime($startdate),strtotime($enddate ." 23:59:59")),"BETWEEN");
							$mysqli->where("md.x0",$variations["company_id"]);
							$mysqli->where("md.x4",PLATFORM_TYPE_SOCIAL_MEDIA);
							$mysqli->where("m.status",IGNORE_STATUS_CODE,"!=");

							$mysqli->where("m.title NOT LIKE 'RT @%'");
							$mysqli->groupBy("m.metaphone");
							$mysqli->orderBy("mention_count","DESC");
							$selectFields = "count(m.id) as mention_count, m.id, m.url, m.metaphone, m.title, m.description, m.post_time, m.author";
							$relatedNews = $mysqli->get(MENTIONS." m", 5 ,$selectFields);
							if(count($relatedNews)):
								foreach($relatedNews as $newsLoop => $newsData):

									if(isset($newsData["author"]) && !empty($newsData["author"])):
										$authorFilter = array("id"=>$newsData["author"]);
										$getAuthorInfo = $confab->get_author_info($authorFilter);

									endif;

									$alerts[$symbol]["news"][$newsData['id']] = array(
															"mention_count" => $newsData["mention_count"],
															"mention_id" => $newsData["id"],
															"url" => $newsData["url"],
															"metaphone" => $newsData["metaphone"],
															"title" => $newsData["title"],
															"description" => $newsData["description"],
															"post_time" => $newsData["post_time"],
															"post_time_display" => date("d M,'y h:i a",$newsData["post_time"]),
															"author" => $newsData["author"],
															);

									if(isset($getAuthorInfo) && !empty($getAuthorInfo))
										$alerts[$symbol]["news"][$newsData['id']]["author_info"] = $getAuthorInfo;

								endforeach;
							endif;

						endif; //todays symbol high cond close.



						//Get Company previous stock price
						$cmpFilter = array("company_id"=>$variations["company_id"],"cmp_time" => $variations["cmp_time"], "limit" => 1);
						$getStockPrevPrice = $confab->get_stock_prev_price($cmpFilter);

						// $confab->printdata($getStockPrevPrice);
						if(isset($getStockPrevPrice) && !empty($getStockPrevPrice)):
							$alerts[$symbol]["variations"][$variations["cmp_time"]]["prev_price"] = number_format($getStockPrevPrice[0]['cmp'],2);
							$alerts[$symbol]["variations"][$variations["cmp_time"]]["prev_cmp_time"] = $getStockPrevPrice[0]['cmp_time'];
							$alerts[$symbol]["variations"][$variations["cmp_time"]]["prev_cmp_time_display"] = date("d M 'y h:i a",$getStockPrevPrice[0]['cmp_time']);

						endif;


					endforeach;


					if(isset($alerts) && count($alerts)):
						$output["code"] = 200;
						$output["payload"] = array("alerts" => $alerts);
						// $output["calc_found_rows"] = $records["calc_found_rows"];
					else:
						$output["code"] = 204;
						$output["msg"] = "No Record Found";

					endif;

					// echo json_encode($output,JSON_UNESCAPED_UNICODE);
					echo json_encode($output,true);


				endif; //tempIf
				
				



			break;


			case 'get_mentions':
				//Get tagged, pending, ignore, raw mention based on token.
				//reset SelectedFilter which is set from dashboard
				$_SESSION['selectedDimension'] = array();
				$filter_param = array();
				if(isset($_POST["filterdata"]) && !empty($_POST["filterdata"])):
					foreach($_POST["filterdata"] as $filterKey => $filterVal):
						if(isset($filterVal["value"]) && !empty($filterVal["value"])):
							$arraykey = trim($filterVal["name"],"[]");
							$arraykey = str_replace("filter_", "", $arraykey);
							//if filter has multiple value store it into array else single variable.
							if(strpos($filterVal["name"], '[]'))
								$filter_param[$arraykey][]=$filterVal["value"];
							else
								$filter_param[$arraykey]=$filterVal["value"];
						endif;
					endforeach;
				endif;

				$_SESSION["startdate"] = (isset($startdate) && !empty($startdate)) ? $startdate : $_SESSION["startdate"];
				$_SESSION["enddate"] = (isset($enddate) && !empty($enddate)) ? $enddate : $_SESSION["enddate"];
				$filter_param["limit"] = (isset($limit) && !empty($limit)) ? $limit : INT_MENTION_PER_PAGE;

				$filter_param["startdate"] = $_SESSION["startdate"];
				$filter_param["enddate"] = $_SESSION["enddate"];
				if(isset($cfbToken) && !empty($cfbToken))
					$_SESSION['cfbToken'] = $cfbToken;

				$_SESSION['filter_param'] = $filter_param;

				$filter_param["cfbToken"] = $cfbToken;

				if(in_array($cfbToken, explode(",", ECONOMY_LINKS))):
					$records = $confab->get_economy_mentions($filter_param);
				else:
					$records = $confab->get_mentions($filter_param);
				endif;

				if(isset($records["payload"]) && count($records["payload"])):
					$output["code"] = 200;
					$output["payload"] = $records["payload"];
					$output["calc_found_rows"] = $records["calc_found_rows"];
				else:
					$output["code"] = 204;
					$output["msg"] = "No Record Found";

				endif;
				// echo json_encode($output,JSON_UNESCAPED_UNICODE);
				echo json_encode($output,true);


			break;

			case 'get-epaper-mentions':

				$param = array();

				$startdate = (isset($startdate) && !empty($startdate)) ? $startdate : START_DATE;
				$enddate = (isset($enddate) && !empty($enddate)) ? $enddate: END_DATE;

				$param['startdate'] = $startdate;
				$param['enddate'] = $enddate;

				if(isset($company_id) && !empty($company_id))
					$param["x0"]=$company_id;

				if(isset($limit) && !empty($limit))
					$param['limit'] = $limit;
				
				$get_epaper = $confab->get_epaper_mentions($param);
             
				$epaper_news = array();
				if(count($get_epaper)):

					$calc_found_rows = 	$get_epaper["calc_found_rows"];	

					if(isset($layout) && !empty($layout) && $layout!=1):

						foreach($get_epaper['payload'] as $epaper_key => $news):
							
							$key_param = ($layout==2) ? $news['x0'] : $news['platform'];	
							$key_heading = 	($layout==2) ? $news['x0_label'] : $news['platform'];				

							if(!array_key_exists($key_param, $epaper_news)):
								$epaper_news[$key_param]['heading'] = $key_heading;
							endif;

							$epaper_news[$key_param]["news"][] = $news;

						endforeach;

					else:	
						$epaper_news = $get_epaper["payload"];	
					endif;
					$epaper_html_file = file_get_contents('account/nseit/templates/partial.epaper-ext.tpl');
					$epaper_html =''; $str_ignore_btn='';$str_update_btn_opt=$str_update_btn='';
					//$widget_body_height = $_SESSION['usr_type']==1 ? 'height-300' : 'height-250';
                    $widget_body_height = 'height-250';
					if(isset($_SESSION['usr_type']) && ($_SESSION['usr_type']==1)):
                        $str_ignore_btn ='<span class="pull-right" id="ignore_epaper_mention_STR_MENTION_ID">
                                            <a href="javascript:void(0);"><i class="fa fa-close text-danger" aria-hidden="true" title="Ignore"></i></a>
                                        </span>';
					   /* $str_update_btn .='<div class="row pull-left margin-vertical-15">
                    					<div class="col-md-8 col-sm-8 col-xs-8">
                        				<select data-placeholder="Choose a Company" class="chosen-select col-md-12" tabindex="1" name="epaper_company" id="epaper_company_STR_MENTION_ID">
                            			<option value="">Select Company</option>
                                        STR_OPTIONS_LIST
                                        </select> 
                    					</div>
										<div class="col-md-3 col-sm-3 col-xs-3 margin-left-20">
											<button class="btn btn-success btn-xs update_comp" id="update_company_STR_MENTION_ID" >Update</button>
										</div>
										</div>';
                            foreach($_SESSION['dimensions']['x0']['child'] as $key=>$value):
                                $selectedField = "";
                                if(in_array($value['id'],$_SESSION['selectedDimension']))
                                $selectedField = "selected";
                                $str_update_btn_opt .='<option value="'.$value['id'].'" '.$selectedField.'>'.$value['dimension_attribute'].'</option>';
                            endforeach;*/
					endif;
					foreach($epaper_news as $epaper_new){
					     $epaper_html .= $epaper_html_file ;
                         // $og_img = !empty($epaper_new['og_image']) ? $epaper_new['og_image'] : "build/assets/common/img/company-logo-placeholder.jpg";
                        $og_img=file_exists("account/nseit/assets/common/img/logo_".$epaper_new['x0'].".jpg") ? "account/nseit/assets/common/img/logo_".$epaper_new['x0'].".jpg" : "build/assets/common/img/company-logo-placeholder.jpg";
                         $epaper_html = str_replace("STR_OG_IMG",$og_img,$epaper_html);                
                         $img_placeholder = "";$pattern_rep ='';
                        if(!empty($epaper_new['og_image'])){
                            $img_placeholder = "style='background-image:url(".$epaper_new['og_image'].")'";
                        }else{
                            $holder_bgcolors = ['#9C27B0', '#0D47A1', '#1B5E20', '#4CAF50', '#FF9800', '#795548', '#607D8B'];
                            $holder_bgidx = (mt_rand() / mt_getrandmax()) + 1; 
                            $epaper_html = str_replace("STR_OG_IMGBG", $holder_bgcolors['holder_bgidx'],$epaper_html);
                            $img_placeholder = 'data-background-src="?holder.js/248x126?bg='.$holder_bgcolors['holder_bgidx'].'&fg='.$holder_bgcolors['holder_bgidx'].'&text=Epaper"';
                        }
                       /* if(isset($_SESSION['usr_type']) && ($_SESSION['usr_type']==1))
                        $pattern_rep = str_replace('value="'.$epaper_new['x0'].'"', 'value="'.$epaper_new['x0'].'" selected',$str_update_btn_opt);
                        */
                        $platform = '<img src=https://www.google.com/s2/favicons?domain='.trim($epaper_new['platform']).'>'.$epaper_new['platform'];
                        $epaper_html = str_replace("STR_MENTION_PLATFORM",$platform,$epaper_html);
                        $epaper_html = str_replace("STR_IGNORE_BTN",$str_ignore_btn,$epaper_html);
                        $epaper_html = str_replace("STR_UPDATE_BTN",$str_update_btn,$epaper_html);
                        $epaper_html = str_replace("STR_OPTIONS_LIST",$pattern_rep,$epaper_html);
						$epaper_html = str_replace('STR_MENTION_ID',$epaper_new['mention_id'],$epaper_html);
						$epaper_html = str_replace('str_img_placeholder',$img_placeholder,$epaper_html);
						$epaper_html = str_replace('STR_COMPANY_NAME',$epaper_new['x0_label'],$epaper_html);
						$epaper_html = str_replace('STR_MENTION_URL',$epaper_new['url'],$epaper_html);
						$epaper_html = str_replace('STR_NEWS_TITLE',$epaper_new['title'],$epaper_html);
						$epaper_html = str_replace('STR_NEWS_DESC',$epaper_new['description'],$epaper_html);
						$epaper_html = str_replace('STR_WIDGET_BODY_HEIGHT',$widget_body_height,$epaper_html);
						$epaper_html = str_replace('STR_MENTION_POSTDATE',$epaper_new['mention_date_format'],$epaper_html);
					}
                    
					$output["code"] = 200;
					$output["usr_type"] = $_SESSION['usr_type'];
					$output['payload'] = $epaper_html;
					$output["calc_found_rows"] = $calc_found_rows;
				else:
					$output["code"] = 204;
					$output["msg"] = "No Record Found";

				endif;

				echo json_encode($output);

			break;

			case 'epaper-update-company':
				if(isset($m_id) && !empty($m_id) && isset($company_id) && !empty($company_id)):
					$md_data = array("x0"=>$company_id);
					$mysqli->where("m_id",$m_id);
					$mysqli->update(EPAPER_MENTIONS_DETAIL_EXT,$md_data,1);
					$output["code"] = 200;	
					$output['payload']["sql"] = $mysqli->getLastQuery();
					echo json_encode($output);
				endif;
			break;

			case 'epaper-update-company':
				if(isset($m_id) && !empty($m_id) && isset($company_id) && !empty($company_id)):
					$md_data = array("x0"=>$company_id);
					$mysqli->where("m_id",$m_id);
					$mysqli->update(EPAPER_MENTIONS_DETAIL_EXT,$md_data,1);
					$output["code"] = 200;	
					$output['payload']["sql"] = $mysqli->getLastQuery();
					echo json_encode($output);
				endif;
			break;

			//For client console we use mention post_time 
			case 'get-dashboard-stats':

				//Get pending mentions stats
				$mysqli->where("status",explode(",", INT_PENDING_STATUS_CODE),"IN");
				$mysqli->where("post_time",strtotime(date("d-m-Y")),">=");
				$pending_records = $mysqli->getOne(MENTIONS,"count(1) as pending_mentions");

				//Get tagged mentions stats
				$mysqli->where("status",LIVE_STATUS_CODE);
				$mysqli->where("post_time",strtotime(date("d-m-Y")),">=");
				$tagged_records = $mysqli->getOne(MENTIONS,"count(1) as tagged_mentions");


				if(count($pending_records) || count($tagged_records)):
					$output["code"] = 200;					
					$output['payload']=array(
											"pending_records" => number_format($pending_records["pending_mentions"]), 
											"tagged_records" => number_format($tagged_records["tagged_mentions"])
											);
					
				else:
					$output["code"] = 204;
					$output["msg"] = "No Record Found";

				endif;

				echo json_encode($output);


			break;
			

			case 'get-companies-alerts':
				$param["min_stock_price"] = MIN_STOCK_PRICE_IN_ALERT;
				$variation_price = $confab->get_variations_with_closing_price($param);
				
				$company_stock_alerts = array();				
				$stock_counter = 1;
				if(count($variation_price)):
					foreach($variation_price as $key => $variations):
						if((!isset($company_stock_alerts[$variations['company_symbol']]) || empty($company_stock_alerts[$variations['company_symbol']])) && $stock_counter<=15):
							$company_stock_alerts[$variations['company_symbol']] = $variations;

							$stock_price = $confab->get_stock_prev_price(array("company_id"=>$variations['company_id'],"limit"=>"1"));

							$company_stock_alerts[$variations['company_symbol']]['cmp'] = $stock_price[0]["cmp"];
							$stock_counter++;
						endif;
					endforeach;
				endif;

				// $confab->printdata($company_stock_alerts);

				// $confab->printdata($companies_recos);
				if(isset($company_stock_alerts) && !empty($company_stock_alerts)):
					$output["code"] = 200;					
					$output['payload']=array_slice($company_stock_alerts,0,15);									
				else:
					$output["code"] = 204;
					$output["msg"] = "No Record Found";
				endif;				
				echo json_encode($output);


			break;


			case 'get-latest-recos':

				$search_param = array("sort_by" => "m.post_time", "sort_order"=>"DESC","limit"=>"0,10");
				if(isset($company_id) && !empty($company_id))
					$search_param['x0'] = $company_id;	

				$startdate = (isset($startdate) && !empty($startdate)) ? $startdate : NULL;
        		$enddate = (isset($enddate) && !empty($enddate)) ? $enddate: NULL;
        
        		$search_param['startdate'] = $startdate;
        		$search_param['enddate'] = $enddate;


				$latest_recos = $confab->get_analyst_recos($search_param);
				// $confab->printdata($_SESSION["analyst_list"]);
				if(isset($latest_recos) && !empty($latest_recos)):
					$output["code"] = 200;					
					$output['payload']=$latest_recos["payload"];
					$output["calc_found_rows"] = $latest_recos["calc_found_rows"];					
				else:
					$output["code"] = 204;
					$output["msg"] = "No Record Found";
				endif;				
				echo json_encode($output);

			break;

			case 'get-recos-donut':

				$search_param = array("sort_by" => "m.post_time", "sort_order"=>"DESC","limit"=>"0,10");
				if(isset($company_id) && !empty($company_id))
					$search_param['x0'] = $company_id;	

				$startdate = (isset($startdate) && !empty($startdate)) ? $startdate : NULL;
        		$enddate = (isset($enddate) && !empty($enddate)) ? $enddate: NULL;
        
        		$search_param['startdate'] = $startdate;
        		$search_param['enddate'] = $enddate;

				$latest_recos = $confab->get_analyst_recos($search_param);
				// $confab->printdata($latest_recos);

				$records = array("Buy"=>0,"Sell"=>0,"Hold"=>0);
				if(count($latest_recos)):
					foreach($latest_recos["payload"] as $recos_key => $reco_info):
						$records[$reco_info['x2_label']] = $records[$reco_info['x2_label']] + 1;
					endforeach;
				endif;


				// $confab->printdata($_SESSION["analyst_list"]);
				if(isset($records) && !empty($records)):
					$output["code"] = 200;					
					$output['payload']=$records;
					// $output["calc_found_rows"] = $latest_recos["calc_found_rows"];					
				else:
					$output["code"] = 204;
					$output["msg"] = "No Record Found";
				endif;				
				echo json_encode($output);

			break;

			case 'get-oneweek-companies-recos':

				$startdate = date("d-m-Y",strtotime("-7 days"));
				$enddate = date("d-m-Y");

				$search_param = array(	"startdate" => $startdate,
										"enddate" => $enddate,
										"sort_by" => "m.post_time", 
										"sort_order"=>"DESC",
										// "limit"=>"0,10"
										);

				$latest_recos = $confab->get_analyst_recos($search_param);
				$companies_recos = array();
				$multisort = array();
				if(count($latest_recos)):
					foreach($latest_recos["payload"] as $recosKey => $recos):
						if(!isset($companies_recos[$recos['company_symbol']]) || empty($companies_recos[$recos['company_symbol']])):
							$companies_recos[$recos['company_symbol']] = array(
														"company_symbol"=>$recos['company_symbol'],
														"x0"=>$recos['x0'],
														"x0_label"=>$recos['x0_label'],
														"reco_count" => 0,
														"Buy" => 0,
														"Sell" => 0,
														"Hold" => 0,
														"cmp" => $recos['cmp']	
														);
						endif;
						$companies_recos[$recos['company_symbol']]["reco_count"] =  $companies_recos[$recos['company_symbol']]['reco_count'] + 1;
						$companies_recos[$recos['company_symbol']][$recos['x2_label']] = $companies_recos[$recos['company_symbol']][$recos['x2_label']] + 1;
						$multisort[$recos['company_symbol']] = $companies_recos[$recos['company_symbol']]['reco_count'] + 1;

					endforeach;
				endif;	


				array_multisort($multisort, SORT_DESC, $companies_recos);

				// $confab->printdata($companies_recos);
				if(isset($latest_recos) && !empty($latest_recos)):
					$output["code"] = 200;					
					$output['payload']=array_slice($companies_recos,0,15);
					$output["calc_found_rows"] = $latest_recos["calc_found_rows"];					
				else:
					$output["code"] = 204;
					$output["msg"] = "No Record Found";
				endif;				
				echo json_encode($output);

			break;

			case 'get-social-media-mentions':
				//Social Media Platform
				$social_media_platforms = array();
				if(isset($_SESSION["site_list"][3])):
					foreach($_SESSION["site_list"][3] as $site_key => $site_info):
						$social_media_platforms[] = $site_info['sitename'];
					endforeach;
				endif;			

				$startdate = (isset($startdate) && !empty($startdate)) ? $startdate : START_DATE;
				$enddate = (isset($enddate) && !empty($enddate)) ? $enddate: END_DATE;

				


				$search_param = array(
										"sort_by" => "m.post_time", 
										"sort_order"=>"DESC",
										"limit"=>"0,50",
										"platform"=>$social_media_platforms,
										"startdate" => $startdate,
										"enddate" => $enddate
									);

				// if(isset($unique) && !empty($unique)):
					$search_param["group_by"] = 1;
					$search_param["group_by_column_name"] = "metaphone";

				// endif;

				if(isset($company_id) && !empty($company_id))
					$search_param['x0'] = $company_id;
			
				$social_media_mentions = $confab->get_site_specific_mentions($search_param);
				
				if(count($social_media_mentions) || count($social_media_mentions)):
					$output["code"] = 200;					
					$output['payload']=$social_media_mentions["payload"];
					$output["calc_found_rows"] = $social_media_mentions["calc_found_rows"];					
				else:
					$output["code"] = 204;
					$output["msg"] = "No Record Found";
				endif;				
				echo json_encode($output);

			break;

			case 'get-top-gainers':


				$company_list = array();
				$slot = (isset($t_slot)) ? $t_slot : 1;

				$get_end_date = $confab->getPreviousDate($slot);

				$current_date_param = array(
								"startdate" => date("d-m-Y",$get_end_date),
								"enddate" => date("d-m-Y",$get_end_date)
								);

				$date_cmp_1 =date_create(date("Y-m-d",time()));
				$date_cmp_2 =date_create(date("Y-m-d",$get_end_date));				
				$diff = date_diff($date_cmp_1,$date_cmp_2);		
				$startDateCount = $diff->d + 1;

				$get_start_date = $confab->getPreviousDate($startDateCount);

				$previous_date_param = array(
								"startdate" => date("d-m-Y",$get_start_date),
								"enddate" => date("d-m-Y",$get_start_date)
								);


				$previous_date_data = $confab->get_mentions_by_sentiment($previous_date_param);
				// $confab->printdata($previous_date_data);

				if(count($previous_date_data) && !empty($previous_date_data)):
					foreach($previous_date_data as $loopCounter => $loopData):

						if(!isset($company_list[$loopData["x0"]])):
							$company_list[$loopData["x0"]]["yesterday"]["Positive"] = 0;
							$company_list[$loopData["x0"]]["yesterday"]["Negative"] = 0;
							$company_list[$loopData["x0"]]["yesterday"]["Neutral"] = 0;

							$company_list[$loopData["x0"]]["today"]["Positive"] = 0;
							$company_list[$loopData["x0"]]["today"]["Negative"] = 0;
							$company_list[$loopData["x0"]]["today"]["Neutral"] = 0;
						endif;


						$company_list[$loopData["x0"]]["yesterday"][$_SESSION["dimensions"]["x1"]["child"][$loopData["x1"]]["dimension_attribute"]] = $loopData["cnt"];
						$company_list[$loopData["x0"]]["company_name"] = $_SESSION["dimensions"]["x0"]["child"][$loopData["x0"]]["dimension_attribute"];
						$company_list[$loopData["x0"]]["company_symbol"] = $_SESSION["dimensions"]["x0"]["child"][$loopData["x0"]]["extra"];
						$company_list[$loopData["x0"]][$_SESSION["dimensions"]["x1"]["child"][$loopData["x1"]]["dimension_attribute"]]["mention_count"] = $loopData["cnt"];
						
					endforeach;
				endif;


				$current_date_data = $confab->get_mentions_by_sentiment($current_date_param);
				// $confab->printdata($current_date_data);

				if(count($current_date_data) && !empty($current_date_data)):
					foreach($current_date_data as $loopCounter => $loopData):

						if(!isset($company_list[$loopData["x0"]])):
							$company_list[$loopData["x0"]]["yesterday"]["Positive"] = 0;
							$company_list[$loopData["x0"]]["yesterday"]["Negative"] = 0;
							$company_list[$loopData["x0"]]["yesterday"]["Neutral"] = 0;

							$company_list[$loopData["x0"]]["today"]["Positive"] = 0;
							$company_list[$loopData["x0"]]["today"]["Negative"] = 0;
							$company_list[$loopData["x0"]]["today"]["Neutral"] = 0;
						endif;


						$company_list[$loopData["x0"]]["today"][$_SESSION["dimensions"]["x1"]["child"][$loopData["x1"]]["dimension_attribute"]] = $loopData["cnt"];
						$company_list[$loopData["x0"]]["company_name"] = $_SESSION["dimensions"]["x0"]["child"][$loopData["x0"]]["dimension_attribute"];
						$company_list[$loopData["x0"]]["company_symbol"] = $_SESSION["dimensions"]["x0"]["child"][$loopData["x0"]]["extra"];
						$company_list[$loopData["x0"]][$_SESSION["dimensions"]["x1"]["child"][$loopData["x1"]]["dimension_attribute"]]["mention_count"] = $loopData["cnt"];
						
					endforeach;
				endif;


				$all_positive =  $all_negative =  array();

				$top_positive = $top_negative =  array();


				if(isset($company_list) && !empty($company_list)):

					foreach($company_list as $loopCounter => $loopData):

						if(isset($loopData['today']['Positive']) && isset($loopData['yesterday']['Positive']) && !empty($loopData['today']['Positive'])):

							if(empty($loopData['yesterday']['Positive'])):
								$all_positive[$loopCounter] = $loopData['today']['Positive'] * 100;
							else:

								$diff = $loopData['today']['Positive'] - $loopData['yesterday']['Positive'];
								$pos = round(( $diff * 100) /  $loopData['yesterday']['Positive'],1); 
								if(isset($pos) && !empty($pos))
									($pos > 0) ? $all_positive[$loopCounter] = $pos : $positiveDown[$loopCounter] = $pos;

							endif;
														
						endif;

							
						if(isset($loopData['today']['Negative']) && isset($loopData['yesterday']['Negative']) && !empty($loopData['today']['Negative'])):

							if(empty($loopData['yesterday']['Positive'])):
								$all_negative[$loopCounter] = $loopData['today']['Negative'] * 100;
							else:

								$diff = $loopData['today']['Negative'] - $loopData['yesterday']['Negative'];
								$pos = round(( $diff * 100) /  $loopData['yesterday']['Negative'],1); 
								if(isset($pos) && !empty($pos))
									($pos >  0) ? $all_negative[$loopCounter] = $pos : $negativeDown[$loopCounter] = $pos;

							endif;

														
						endif;						

					endforeach;



					arsort($all_positive);
					// asort($positiveDown);
					arsort($all_negative);
					// asort($negativeDown);


					if(count($all_positive)<=5)
						$top_positive = $all_positive;
					else
						$top_positive = array_slice($all_positive, 0, 5,true);

					if(count($all_negative)<=5)
						$top_negative = $all_negative;
					else
						$top_negative = array_slice($all_negative, 0, 5,true);



					//Get Top Positive Gainer
					$top_gainer_postive = array();
					$counter = 0;

					$stockEndDate = time();  //Current time 
					$stockStartDate = strtotime(date("d-m-Y",strtotime('-6 days',$stockEndDate)));

					foreach($top_positive as $companyId => $sentimentChange):
						$top_gainer_postive[$counter]["sentimentUpBy"] = number_format($sentimentChange);
						$top_gainer_postive[$counter]['company_id'] = $companyId;
						$top_gainer_postive[$counter]['company_name'] = $company_list[$companyId]["company_name"];
						$top_gainer_postive[$counter]['company_symbol'] = $company_list[$companyId]["company_symbol"];
						$top_gainer_postive[$counter]['mention_count'] = $company_list[$companyId]["Positive"]["mention_count"];


						$mysqli->where("x0_id",$companyId);
						$mysqli->where("start_close_price_flag",CLOSE_PRICE_FLAG);
						$stock_details = "FROM_UNIXTIME(cmp_time,'%d-%m-%Y') as cdate, cmp, cmp_time";
						$mysqli->orderBy("cmp_time","DESC");
						$stock_closing_price = $mysqli->get(STOCK_CMP,7,$stock_details);
						if(count($stock_closing_price)):
							$stock_closed_at = array();
							foreach($stock_closing_price as $stock_cmp_time => $stock_info):
								$stock_closed_at[$stock_info['cmp_time']] = array(
																					"cdate"=>$stock_info['cdate'],
																					"cmp"=>$stock_info['cmp'],
																				);
							endforeach;
							$top_gainer_postive[$counter]["stockMovement"] = $stock_closed_at;			
						endif;	
									
						$counter++;	
					endforeach;


					//Get Top Negative Gainer
					$top_gainer_negative = array();
					$counter = 0;
					foreach($top_negative as $companyId => $sentimentChange):
						$top_gainer_negative[$counter]["sentimentUpBy"] = number_format($sentimentChange);
						$top_gainer_negative[$counter]['company_id'] = $companyId;
						$top_gainer_negative[$counter]['company_name'] = $company_list[$companyId]["company_name"];
						$top_gainer_negative[$counter]['company_symbol'] = $company_list[$companyId]["company_symbol"];
						$top_gainer_negative[$counter]['mention_count'] = $company_list[$companyId]["Negative"]["mention_count"];

	

						$mysqli->where("x0_id",$companyId);
						$mysqli->where("start_close_price_flag",CLOSE_PRICE_FLAG);

						$stock_details = "FROM_UNIXTIME(cmp_time,'%d-%m-%Y') as cdate, cmp, cmp_time";

						$mysqli->orderBy("cmp_time","DESC");
						$stock_closing_price = $mysqli->get(STOCK_CMP,7,$stock_details);

						if(count($stock_closing_price)):
							$stock_closed_at = array();
							foreach($stock_closing_price as $stock_cmp_time => $stock_info):
								$stock_closed_at[$stock_info['cmp_time']] = array(
																					"cdate"=>$stock_info['cdate'],
																					"cmp"=>$stock_info['cmp'],
																				);
							endforeach;
							$top_gainer_negative[$counter]["stockMovement"] = $stock_closed_at;			
						endif;	

						$counter++;		
					endforeach;	

					$output["code"] = 200;
					$output["payload"] = array(
											"top_gainer_postive" => $top_gainer_postive,
											"top_gainer_negative" => $top_gainer_negative,
											"start_date"=>$current_date_param['startdate'],
											"enddate"=>$current_date_param['enddate']
											);
					echo json_encode($output,true);

				endif;  //company_list loop end

			break;

			case 'get-top-gainers-calls':

				$company_list = array();
				$slot = (isset($t_slot)) ? $t_slot : 1;

				$get_end_date = $confab->getPreviousDate($slot);

				$current_date_param = array(
								"startdate" => date("d-m-Y",$get_end_date),
								"enddate" => date("d-m-Y",$get_end_date)
								);

				$date_cmp_1 =date_create(date("Y-m-d",time()));
				$date_cmp_2 =date_create(date("Y-m-d",$get_end_date));				
				$diff = date_diff($date_cmp_1,$date_cmp_2);		
				$startDateCount = $diff->d + 1;

				$get_start_date = $confab->getPreviousDate($startDateCount);

				$previous_date_param = array(
								"startdate" => date("d-m-Y",$get_start_date),
								"enddate" => date("d-m-Y",$get_start_date)
								);

				//Get recommendation
				// $getYestRecommendtn = $confab->getMenGrpByRecommendtn($yest_param);

				$previous_date_data = $confab->get_mentions_by_recommendtn($previous_date_param);
				// $confab->printdata($previous_date_data);

				if(count($previous_date_data) && !empty($previous_date_data)):
					foreach($previous_date_data as $loopCounter => $loopData):

						if(!isset($company_list[$loopData["x0"]])):
							$company_list[$loopData["x0"]]["yesterday"]["Buy"] = 0;
							$company_list[$loopData["x0"]]["yesterday"]["Sell"] = 0;
							$company_list[$loopData["x0"]]["today"]["Buy"] = 0;
							$company_list[$loopData["x0"]]["today"]["Sell"] = 0;
						endif;


						$company_list[$loopData["x0"]]["yesterday"][$_SESSION["dimensions"]["x2"]["child"][$loopData["x2"]]["dimension_attribute"]] = $loopData["cnt"];
						$company_list[$loopData["x0"]]["company_name"] = $_SESSION["dimensions"]["x0"]["child"][$loopData["x0"]]["dimension_attribute"];
						$company_list[$loopData["x0"]]["company_symbol"] = $_SESSION["dimensions"]["x0"]["child"][$loopData["x0"]]["extra"];
						$company_list[$loopData["x0"]][$_SESSION["dimensions"]["x2"]["child"][$loopData["x2"]]["dimension_attribute"]]["mention_count"] = $loopData["cnt"];
						
					endforeach;
				endif;



				$current_date_data = $confab->get_mentions_by_recommendtn($current_date_param);
				// $confab->printdata($current_date_data);

				if(count($current_date_data) && !empty($current_date_data)):
					foreach($current_date_data as $loopCounter => $loopData):

						if(!isset($company_list[$loopData["x0"]])):
							$company_list[$loopData["x0"]]["yesterday"]["Buy"] = 0;
							$company_list[$loopData["x0"]]["yesterday"]["Sell"] = 0;
							$company_list[$loopData["x0"]]["today"]["Buy"] = 0;
							$company_list[$loopData["x0"]]["today"]["Sell"] = 0;
						endif;


						$company_list[$loopData["x0"]]["today"][$_SESSION["dimensions"]["x2"]["child"][$loopData["x2"]]["dimension_attribute"]] = $loopData["cnt"];
						$company_list[$loopData["x0"]]["company_name"] = $_SESSION["dimensions"]["x0"]["child"][$loopData["x0"]]["dimension_attribute"];
						$company_list[$loopData["x0"]]["company_symbol"] = $_SESSION["dimensions"]["x0"]["child"][$loopData["x0"]]["extra"];
						$company_list[$loopData["x0"]][$_SESSION["dimensions"]["x2"]["child"][$loopData["x2"]]["dimension_attribute"]]["mention_count"] = $loopData["cnt"];
						
					endforeach;
				endif;



				$all_buy_calls =  $all_sell_calls = $top_buy_calls =  $top_sell_calls = array();

				if(isset($company_list) && !empty($company_list)):

					foreach($company_list as $loopCounter => $loopData):

						if(isset($loopData['today']['Buy']) && isset($loopData['yesterday']['Buy']) && !empty($loopData['today']['Buy'])):

							if(empty($loopData['yesterday']['Buy'])):
								$all_buy_calls[$loopCounter] = $loopData['today']['Buy'] * 100;
							else:
								$diff = $loopData['today']['Buy'] - $loopData['yesterday']['Buy'];
								$pos = round(( $diff * 100) /  $loopData['yesterday']['Buy'],1); 
								if(isset($pos) && !empty($pos))
									($pos > 0) ? $all_buy_calls[$loopCounter] = $pos : $buyDown[$loopCounter] = $pos;
							endif;							
							
						endif;
						


						if(isset($loopData['today']['Sell']) && isset($loopData['yesterday']['Sell']) && !empty($loopData['today']['Sell']) ):

							if(empty($loopData['yesterday']['Sell'])):
								$all_sell_calls[$loopCounter] = $loopData['today']['Sell'] * 100;
							else:

								$diff = $loopData['today']['Sell'] - $loopData['yesterday']['Sell'];
								$pos = round(( $diff * 100) /  $loopData['yesterday']['Sell'],1); 
								if(isset($pos) && !empty($pos))
									($pos > 0) ? $all_sell_calls[$loopCounter] = $pos : $sellDown[$loopCounter] = $pos;
							endif;							
						endif;	


					endforeach;
	
					arsort($all_buy_calls);
					arsort($all_sell_calls);


					if(count($all_buy_calls)<=5)
						$top_buy_calls = $all_buy_calls;
					else
						$top_buy_calls = array_slice($all_buy_calls, 0, 5,true);


					if(count($all_sell_calls)<=5)
						$top_sell_calls = $all_sell_calls;
					else
						$top_sell_calls = array_slice($all_sell_calls, 0, 5,true);



					//Get Top Buy Call
					$top_gainer_buy_call = array();
					$counter = 0;
					// $stockEndDate = strtotime($curr_param['enddate']." 23:59:59");
					$stockEndDate = time();
					$stockStartDate = strtotime(date("d-m-Y",strtotime('-6 day',$stockEndDate)));
					foreach($top_buy_calls as $companyId => $recommChange):


						$top_gainer_buy_call[$counter]["recommUpBy"] = number_format($recommChange);
						$top_gainer_buy_call[$counter]['company_id'] = $companyId;
						$top_gainer_buy_call[$counter]['company_name'] = $company_list[$companyId]["company_name"];
						$top_gainer_buy_call[$counter]['company_symbol'] = $company_list[$companyId]["company_symbol"];
						$top_gainer_buy_call[$counter]['mention_count'] = $company_list[$companyId]["Buy"]["mention_count"];


						$mysqli->where("x0_id",$companyId);
						$mysqli->where("start_close_price_flag",CLOSE_PRICE_FLAG);
						$stock_details = "FROM_UNIXTIME(cmp_time,'%d-%m-%Y') as cdate, cmp, cmp_time";
						$mysqli->orderBy("cmp_time","DESC");
						$stock_closing_price = $mysqli->get(STOCK_CMP,7,$stock_details);
						if(count($stock_closing_price)):
							$stock_closed_at = array();
							foreach($stock_closing_price as $stock_cmp_time => $stock_info):
								$stock_closed_at[$stock_info['cmp_time']] = array(
																					"cdate"=>$stock_info['cdate'],
																					"cmp"=>$stock_info['cmp'],
																				);
							endforeach;
							$top_gainer_buy_call[$counter]["stockMovement"] = $stock_closed_at;			
						endif;					
						
						$counter++;	
					endforeach;



					//Get Top Sell Call
					$top_gainer_sell_call = array();
					$counter = 0;
					foreach($top_sell_calls as $companyId => $recommChange):
						if($counter < 5):
							$top_gainer_sell_call[$counter]["recommUpBy"] = number_format($recommChange);
							$top_gainer_sell_call[$counter]['company_id'] = $companyId;
							$top_gainer_sell_call[$counter]['company_name'] = $company_list[$companyId]["company_name"];
							$top_gainer_sell_call[$counter]['company_symbol'] = $company_list[$companyId]["company_symbol"];
							$top_gainer_sell_call[$counter]['mention_count'] = $company_list[$companyId]["Sell"]["mention_count"];

							$mysqli->where("x0_id",$companyId);
							$mysqli->where("start_close_price_flag",CLOSE_PRICE_FLAG);
							$stock_details = "FROM_UNIXTIME(cmp_time,'%d-%m-%Y') as cdate, cmp, cmp_time";
							$mysqli->orderBy("cmp_time","DESC");
							$stock_closing_price = $mysqli->get(STOCK_CMP,7,$stock_details);
							if(count($stock_closing_price)):
								$stock_closed_at = array();
								foreach($stock_closing_price as $stock_cmp_time => $stock_info):
									$stock_closed_at[$stock_info['cmp_time']] = array(
																						"cdate"=>$stock_info['cdate'],
																						"cmp"=>$stock_info['cmp'],
																					);
								endforeach;
								$top_gainer_sell_call[$counter]["stockMovement"] = $stock_closed_at;			
							endif;	
						endif;
						$counter++;		
					endforeach;


					$output["code"] = 200;
					$output["payload"] = array(
												"top_gainer_buy_call" => $top_gainer_buy_call,
												"top_gainer_sell_call" => $top_gainer_sell_call,
												"start_date"=>$current_date_param['startdate'],
												"enddate"=>$current_date_param['enddate']
												);
					echo json_encode($output,true);

				endif;





			break;

			case 'get-top-gainers-valume':

				$company_list = array();
				$slot = (isset($t_slot)) ? $t_slot : 1;

				$get_end_date = $confab->getPreviousDate($slot);

				$current_date_param = array(
								"startdate" => date("d-m-Y",$get_end_date),
								"enddate" => date("d-m-Y",$get_end_date)
								);

				$date_cmp_1 =date_create(date("Y-m-d",time()));
				$date_cmp_2 =date_create(date("Y-m-d",$get_end_date));				
				$diff = date_diff($date_cmp_1,$date_cmp_2);		
				$startDateCount = $diff->d + 1;

				$get_start_date = $confab->getPreviousDate($startDateCount);

				$previous_date_param = array(
								"startdate" => date("d-m-Y",$get_start_date),
								"enddate" => date("d-m-Y",$get_start_date)
								);

				//Get recommendation
				// $getYestRecommendtn = $confab->getMenGrpByRecommendtn($yest_param);

				$previous_date_data = $confab->get_mentions_by_company($previous_date_param);
				// $confab->printdata($previous_date_data);

				if(count($previous_date_data) && !empty($previous_date_data)):
					foreach($previous_date_data as $loopCounter => $loopData):

						if(!isset($company_list[$loopData["x0"]])):
							$company_list[$loopData["x0"]]["yesterday"]["tagged"] = 0;
							$company_list[$loopData["x0"]]["today"]["tagged"] = 0;
						endif;

						$company_list[$loopData["x0"]]["yesterday"]["tagged"] = $loopData["cnt"];
						$company_list[$loopData["x0"]]["company_name"] = $_SESSION["dimensions"]["x0"]["child"][$loopData["x0"]]["dimension_attribute"];
						$company_list[$loopData["x0"]]["company_symbol"] = $_SESSION["dimensions"]["x0"]["child"][$loopData["x0"]]["extra"];
						$company_list[$loopData["x0"]]["mention_count"] = $loopData["cnt"];
						
					endforeach;
				endif;



				$current_date_data = $confab->get_mentions_by_company($current_date_param);
				// $confab->printdata($current_date_data);

				if(count($current_date_data) && !empty($current_date_data)):
					foreach($current_date_data as $loopCounter => $loopData):
						if(!isset($company_list[$loopData["x0"]])):
							$company_list[$loopData["x0"]]["yesterday"]["tagged"] = 0;
							$company_list[$loopData["x0"]]["today"]["tagged"] = 0;
						endif;						


						$company_list[$loopData["x0"]]["today"]["tagged"] = $loopData["cnt"];
						$company_list[$loopData["x0"]]["company_name"] = $_SESSION["dimensions"]["x0"]["child"][$loopData["x0"]]["dimension_attribute"];
						$company_list[$loopData["x0"]]["company_symbol"] = $_SESSION["dimensions"]["x0"]["child"][$loopData["x0"]]["extra"];
						$company_list[$loopData["x0"]]["mention_count"] = $loopData["cnt"];
						
					endforeach;
				endif;



				//Yesterday and current day mention movement
				$valume_up = $valume_down = array();
				if(count($company_list)):
					foreach($company_list as $loopCounter => $loopData):

						if(isset($loopData['today']['tagged']) && isset($loopData['yesterday']['tagged']) && !empty($loopData['today']['tagged']) ):

							if(empty($loopData['yesterday']['tagged'])):
								$valume_up[$loopCounter] = $loopData['today']['tagged'] * 100;
							else:
								$diff = $loopData['today']['tagged'] - $loopData['yesterday']['tagged'];
								$pos = round(( $diff * 100) /  $loopData['yesterday']['tagged'],1); 
								if(isset($pos) && !empty($pos))
									($pos > 0) ? $valume_up[$loopCounter] = $pos : $valume_down[$loopCounter] = $pos;	

							endif;

															
						endif;

					endforeach;

					arsort($valume_up);
							

					if(count($valume_up)<=5)
						$valume_up = $valume_up;
					else
						$valume_up = array_slice($valume_up, 0, 5,true);

					

					//Get Top Volume Change
					$top_volume_up = array();
					$counter = 0;
					$stockEndDate = time();
					$stockStartDate = strtotime(date("d-m-Y",strtotime('-6 day',$stockEndDate)));
					foreach($valume_up as $companyId => $volumeChange):
						$top_volume_up[$counter]["volumeUpBy"] = number_format($volumeChange);
						$top_volume_up[$counter]['company_id'] = $companyId;
						$top_volume_up[$counter]['company_name'] = $company_list[$companyId]["company_name"];
						$top_volume_up[$counter]['company_symbol'] = $company_list[$companyId]["company_symbol"];
						$top_volume_up[$counter]['mention_count'] = $company_list[$companyId]["mention_count"];

						$mysqli->where("x0_id",$companyId);
						$mysqli->where("start_close_price_flag",CLOSE_PRICE_FLAG);
						$stock_details = "FROM_UNIXTIME(cmp_time,'%d-%m-%Y') as cdate, cmp, cmp_time";
						$mysqli->orderBy("cmp_time","DESC");
						$stock_closing_price = $mysqli->get(STOCK_CMP,7,$stock_details);
						if(count($stock_closing_price)):
							$stock_closed_at = array();
							foreach($stock_closing_price as $stock_cmp_time => $stock_info):
								$stock_closed_at[$stock_info['cmp_time']] = array(
																					"cdate"=>$stock_info['cdate'],
																					"cmp"=>$stock_info['cmp'],
																				);
							endforeach;
							$top_volume_up[$counter]["stockMovement"] = $stock_closed_at;			
						endif;						
						
						$counter++;	
					endforeach;

					$output["code"] = 200;
					$output["payload"] = array(
												"top_volume_up" => $top_volume_up,											
												"start_date"=>$current_date_param['startdate'],
												"enddate"=>$current_date_param['enddate']
												);
					echo json_encode($output,true);
				endif;



			break;



			case 'latest-company-mentions':
				if(isset($cfbToken) && !empty($cfbToken))
					$_SESSION['cfbToken'] = $cfbToken;


				$startdate = (isset($startdate) && !empty($startdate)) ? $startdate : START_DATE;
				$enddate = (isset($enddate) && !empty($enddate)) ? $enddate: END_DATE;

				
				$search_param = array(	
										"startdate" => $startdate,
										"enddate" => $enddate,
										"priority" => 1,
										"limit"=>"0,30",

									);				

				if(isset($company_id) && !empty($company_id))
					$search_param['x0'] = $company_id;

				$records = $confab->get_mentions($search_param);
				// $confab->printdata($records);
				$output = array();
				if(isset($records["payload"]) && count($records["payload"])):
					$output["code"] = 200;
					$output["payload"] = $records["payload"];
					$output["calc_found_rows"] = $records["calc_found_rows"];
				else:
					$output["code"] = 204;
					$output["msg"] = "No Record Found";

				endif;
				// echo json_encode($output,JSON_UNESCAPED_UNICODE);
				echo json_encode($output,true);

			break;


			

			case 'latest-economy-mentions':

				if(isset($cfbToken) && !empty($cfbToken))
					$_SESSION['cfbToken'] = $cfbToken;

				$search_param = array("limit"=>"0,10");
				$records = $confab->get_economy_mentions($search_param);
				// $confab->printdata($records);
				$output = array();
				if(isset($records["payload"]) && count($records["payload"])):
					$output["code"] = 200;
					$output["payload"] = $records["payload"];
					$output["calc_found_rows"] = $records["calc_found_rows"];
				else:
					$output["code"] = 204;
					$output["msg"] = "No Record Found";

				endif;
				// echo json_encode($output,JSON_UNESCAPED_UNICODE);
				echo json_encode($output,true);
			break;
			

			case 'latest-annoucements':

				$search_param = array(										
										"startdate" => date("d-m-Y",strtotime("-1 days")),
										"enddate" => date("d-m-Y")
									);

				$sebi_announcement = $confab->get_sebi_announcement($search_param);

				$get_mc_announcement = $confab->get_company_announcement($search_param);
				$mc_announcement = array();
				if(count($get_mc_announcement)):
					foreach($get_mc_announcement as $mc_announcement_key => $mc_announcement_info):
						$mc_announcement[] = array(
													"id" => $mc_announcement_info['id'],
													"comp_id" => $mc_announcement_info['comp_id'],
													"title" => $mc_announcement_info['title'],
													"description" => $mc_announcement_info['description'],
													"url" => $mc_announcement_info['url'],
													"platform" => $mc_announcement_info['platform'],
													"raw_time" => $mc_announcement_info['raw_time'],
													"post_time" => $mc_announcement_info['post_time'],
													"post_time_display_date" => date("M d,Y",$mc_announcement_info['post_time']),
													"insert_time" => $mc_announcement_info['insert_time'],
												);
					endforeach;
				endif;

				if(count($sebi_announcement) || count($mc_announcement)):
					$output["code"] = 200;
					$output["payload"]['sebi_announcement'] = $sebi_announcement;
					$output["payload"]['mc_announcement'] = $mc_announcement;
				else:
					$output["code"] = 204;
					$output["msg"] = "No Record Found";
				endif;

				echo json_encode($output,true);
			break;

			case 'sebi-annoucements':

				$startdate = (isset($startdate) && !empty($startdate)) ? $startdate : START_DATE;
				$enddate = (isset($enddate) && !empty($enddate)) ? $enddate: END_DATE;
				$param['startdate'] = $startdate;
				$param['enddate'] = $enddate;

				if(isset($company_id) && !empty($company_id))
					$param["company_id"]=$company_id;

				$sebi_announcement = $confab->get_sebi_announcement($param);

				$all_announcements = array();
				if(count($sebi_announcement)):
					foreach($sebi_announcement as $announcement_keys => $announcements_info):
						$post_time = date('Y-m-d',$announcements_info['post_time']);
						$insert_time =   date('Y-m-d',$announcements_info['insert_time']);
						$announcement = array(
											'platform'=>trim($announcements_info['platform']),
											'title'=>trim($announcements_info['title']),
											'description'=>trim($announcements_info['description']),
											'post_time' => $announcements_info['post_time'],
											'post_time_display'=>date('d F Y',$announcements_info['post_time']),
											'insert_time' => $announcements_info['insert_time'],
											'insert_time_display'=> date('d F, Y',$announcements_info['insert_time']),
											'insert_time_display_2'=> date('d F Y',$announcements_info['insert_time']),	
											'url'=>$announcements_info['url']
											);
						$announcements_flag = ($post_time!=$insert_time) ? true : false;
						$announcement['announcements_flag'] = $announcements_flag;
						if($post_time!=$insert_time):							
							$updated_announcement[date('l, d F Y',$announcements_info['post_time'])][] = $announcement;
						endif;	
						$all_announcements[date('l, d F Y',$announcements_info['post_time'])][] = $announcement;
					endforeach;
				endif;

				if(count($all_announcements) || count($all_announcements)):
					$output["code"] = 200;
					$output["payload"]['updated_announcement'] = $updated_announcement;
					$output["payload"]['all_announcements'] = $all_announcements;
				else:
					$output["code"] = 204;
					$output["msg"] = "No Record Found";
				endif;

				echo json_encode($output,true);

			break;

			case 'get-highlighted-mentions':

				if(isset($cfbToken) && !empty($cfbToken))
					$_SESSION['cfbToken'] = $cfbToken;

			

				$search_param = array(
										"author"=>"equitybulls",
										"limit"=>"0,30",
										"except_ignore"=>1,
										"startdate" => date("d-m-Y",strtotime("-7 days")),
										"enddate" => date("d-m-Y")
									);
				$records = $confab->get_mentions($search_param);
				// $confab->printdata($records);
				$output = array();
				if(isset($records["payload"]) && count($records["payload"])):
					$output["code"] = 200;
					$output["payload"] = $records["payload"];
					$output["calc_found_rows"] = $records["calc_found_rows"];
				else:
					$output["code"] = 204;
					$output["msg"] = "No Record Found";

				endif;
				// echo json_encode($output,JSON_UNESCAPED_UNICODE);
				echo json_encode($output,true);


			break;

			case 'get-market-outlook':

				if(isset($cfbToken) && !empty($cfbToken))
					$_SESSION['cfbToken'] = $cfbToken;

				$search_param = array();
				$search_param['cfbToken'] = $cfbToken;

				$startdate = (isset($startdate) && !empty($startdate)) ? $startdate : date("d-m-Y",strtotime("-1 days"));
				$enddate = (isset($enddate) && !empty($enddate)) ? $enddate: date("d-m-Y");
				
				$search_param['startdate'] = $startdate;
				$search_param['enddate'] = $enddate;


				$search_param["limit"] = "0,30";
				$search_param["x1"] = MARKET_OUTLOOK_ID;

				$records = $confab->get_economy_mentions($search_param);
				// $confab->printdata($records);
				$output = array();
				if(isset($records["payload"]) && count($records["payload"])):
					$output["code"] = 200;
					$output["payload"] = $records["payload"];
					$output["calc_found_rows"] = $records["calc_found_rows"];
				else:
					$output["code"] = 204;
					$output["msg"] = "No Record Found";

				endif;
				// echo json_encode($output,JSON_UNESCAPED_UNICODE);
				echo json_encode($output,true);

			break;

			case 'billing':
				// $month = (isset($month) && !empty($month)) ? $month : 0;
				// $startdate = date("01-m-Y",strtotime("-".$month." months"));
				// $enddate = date("t-m-Y",strtotime("-".$month." months"));
				$enddate =$month;
				$m_name=date('F', strtotime($enddate));
				$output['enddate']=$enddate;
				$output['m_name']=$m_name;
				//$month=date_parse_from_format("d-m-Y", $month);
				// $month = $month['month'];
				
				//$month = (isset($month) && !empty($month)) ? $month : 0;
				//$output['month']=$month;
				// $startdate = '2017-04-01'; //date("01-" . $daterange);
				// $enddate = '2017-04-30'; //date("t-" . $daterange);
				
				$startdate  = date("01-m-Y",strtotime($enddate));
				$output['startdate']=$startdate;
				// print_r($output);exit;
				
				
				$applicable_month = date("M Y",strtotime($startdate));

				$param = array("startdate"=>$startdate,"enddate"=>$enddate,'cfbToken'=>$cfbToken);
				
				$result = $confab->get_tagged_mention_count($param);
				

				$output["code"]=200;
				if(isset($result) && !empty($result)):
					$output["payload"]["tagged_mention_reg"] = number_format($result);					

					$mysqli->where("status",LIVE_STATUS_CODE);
					$mysqli->where("post_time",array(strtotime($startdate),strtotime($enddate." 23:59:59")),"BETWEEN");
					$get_economy_count = $mysqli->getOne(ECONOMICS_MENTIONS,"count(*) as cnt");

					$output["payload"]["tagged_mention_economy"] = number_format($get_economy_count["cnt"]);

					$result = $result + $get_economy_count["cnt"];

					$output["payload"]["tagged_mention"] = $result;
					$output["payload"]["tagged_mention_display"] = number_format($result);
					$output["payload"]["tagged_avg"] = ceil($result/MIN_DAY_PER_MONTH);
					$output["payload"]["tagged_avg_display"] = number_format(ceil($result/MIN_DAY_PER_MONTH));
					$output["payload"]["month"] = $applicable_month;
					$pre_key_slot_high_range = 0;
					foreach($payment_slot as $slot_Key => $slot_info):

						$current_slot_high_range = $slot_info["daily_number"];

						$next_slot_high_range = (($slot_info["daily_number"] * 10) /100) + $slot_info["daily_number"];
					
						if($output["payload"]["tagged_avg"] >= $next_slot_high_range):
							$output["payload"]["payment_slot"] = $payment_slot[$slot_Key+1]['slot_label'];
						else:
							$output["payload"]["payment_slot"] = $payment_slot[$slot_Key]['slot_label'];
							break;
						endif;

					endforeach;
				else:
					$output["payload"]["tagged_mention"] = 0;
				endif;

					$output["payload"]["slot"] = $payment_slot;
					echo json_encode($output,JSON_UNESCAPED_UNICODE);
			break;

			//previous months billing 
			case 'billing_historical':
				$month = (isset($month) && !empty($month)) ? $month : 0;
				// $startdate = date("01-m-Y",strtotime("-".$month." months"));
				// $startdate = date("01-m-Y",strtotime("-5 months"));
				$startdate = "01-06-2016";
				$enddate = date("t-m-Y",strtotime("-".$month." months"));
				
				// $month = (isset($month) && !empty($month)) ? $month : 0;
				// $startdate = '2017-04-01'; //date("01-" . $daterange);
				// $enddate = '2017-04-30'; //date("t-" . $daterange);
				
				
				$applicable_month = date("M Y",strtotime($startdate));

				$param = array("startdate"=>$startdate,"enddate"=>$enddate,'cfbToken'=>$cfbToken);
				
				$result = $confab->get_tagged_mention_count_monthwise($param);
				
				// $query=$mysqli->getLastQuery();
				// echo $query;
				

				$output["code"]=200;
				if(isset($result) && !empty($result)):
					// $output["payload"]["tagged_mention_reg"] = number_format($result);	
					$mysqli->where("status",LIVE_STATUS_CODE);
					$mysqli->where("post_time",array(strtotime($startdate),strtotime($enddate." 23:59:59")),"BETWEEN");
					$mysqli->groupBy("monthYear");
					$mysqli->orderBy("post_time");
					//$mysqli->groupBy(DATE_FORMAT("post_time", "%Y-%m-01"));
					
					$get_economy_count = $mysqli->get(ECONOMICS_MENTIONS,null,"count(*) as tagged,DATE_FORMAT(date(from_unixtime(post_time)),'%b-%Y') as monthYear");
					// echo "<pre>";
					// print_r($get_economy_count);exit;

					//adding both counts 
					$cnt=count($get_economy_count);
					for($i=0;$i<$cnt;$i++){
						$finalResult[$get_economy_count[$i]['monthYear']]=$get_economy_count[$i]['tagged']+$result[$i]['tagged'];

					}
					
					// $query=$mysqli->getLastQuery();
					// echo $query;

					

					//print_r($output);
					

					$pre_key_slot_high_range = 0;
					$invoice_id = 0;
					foreach ($finalResult as $fkey => $fvalue) {
						$output["payload"][$fkey]["invoice_id"] = "M00".$invoice_id;
						$output["payload"][$fkey]["tagged_mention"] = $fvalue;
						$output["payload"][$fkey]["tagged_mention_display"] = number_format($fvalue);
						$output["payload"][$fkey]["tagged_avg"] = ceil($fvalue/MIN_DAY_PER_MONTH);
						$output["payload"][$fkey]["tagged_avg_display"] = number_format(ceil($fvalue/MIN_DAY_PER_MONTH));
						//$output["payload"][$fkey]["month"] = $applicable_month;
						foreach($payment_slot as $slot_Key => $slot_info):

							$current_slot_high_range = $slot_info["daily_number"];

							$next_slot_high_range = (($slot_info["daily_number"] * 10) /100) + $slot_info["daily_number"];
						
							if($output["payload"][$fkey]["tagged_avg"] >= $next_slot_high_range):
								$output["payload"][$fkey]["payment_slot"] = $payment_slot[$slot_Key+1]['slot_label'];
							else:
								$output["payload"][$fkey]["payment_slot"] = $payment_slot[$slot_Key]['slot_label'];
								break;
							endif;

						endforeach;
						$invoice_id++;

					}

				else:
					$output["payload"]["tagged_mention"] = 0;
				endif;

					//$output["payload"]["slot"] = $payment_slot;
					echo json_encode($output,JSON_UNESCAPED_UNICODE);
			break;

			//Company Dashboard
			case 'get-company-info':
				$output = array();
				$output["code"] = 204;
				$output["msg"] = "No Record Found";
				if(isset($company_id) && !empty($company_id)):
					if(isset($_SESSION['dimensions']['x0']['child'][$company_id]) && !empty($_SESSION['dimensions']['x0']['child'][$company_id])):

						$output['payload']['company_name'] =  (isset($_SESSION['dimensions']['x0']['child'][$company_id]['dimension_attribute']) && !empty($_SESSION['dimensions']['x0']['child'][$company_id]['dimension_attribute'])) ? $_SESSION['dimensions']['x0']['child'][$company_id]['dimension_attribute'] : '';

						$output['payload']['company_symbol'] =  (isset($_SESSION['dimensions']['x0']['child'][$company_id]['extra']) && !empty($_SESSION['dimensions']['x0']['child'][$company_id]['extra'])) ? $_SESSION['dimensions']['x0']['child'][$company_id]['extra'] : '';

						$output['payload']['company_website'] = (isset($_SESSION['dimensions']['x0']['child'][$company_id]['comp_website']) && !empty($_SESSION['dimensions']['x0']['child'][$company_id]['comp_website'])) ? $_SESSION['dimensions']['x0']['child'][$company_id]['comp_website'] : '';

						// $output['payload']['company_logo'] = (isset($_SESSION['dimensions']['x0']['child'][$company_id]['comp_logo']) && !empty($_SESSION['dimensions']['x0']['child'][$company_id]['comp_logo'])) ? $_SESSION['dimensions']['x0']['child'][$company_id]['comp_logo'] : '';

						$output['payload']['company_logo'] = file_exists('account/nseit/assets/common/img/logo_'.$company_id.'.jpg') ? 'account/nseit/assets/common/img/logo_'.$company_id.'.jpg' : '';

						$output['payload']['company_location'] = (isset($_SESSION['dimensions']['x0']['child'][$company_id]['comp_location']) && !empty($_SESSION['dimensions']['x0']['child'][$company_id]['comp_location'])) ? $_SESSION['dimensions']['x0']['child'][$company_id]['location'] : '';

						$output['payload']['company_sector'] = (isset($_SESSION['dimensions']['x0']['child'][$company_id]['comp_sector']) && !empty($_SESSION['dimensions']['x0']['child'][$company_id]['comp_sector'])) ? $_SESSION['dimensions']['x0']['child'][$company_id]['comp_sector'] : '';

						$output['payload']['company_fb_url'] = (isset($_SESSION['dimensions']['x0']['child'][$company_id]['comp_fb_url']) && !empty($_SESSION['dimensions']['x0']['child'][$company_id]['comp_fb_url'])) ? $_SESSION['dimensions']['x0']['child'][$company_id]['comp_fb_url'] : '';

						$output['payload']['company_twt_url'] = (isset($_SESSION['dimensions']['x0']['child'][$company_id]['comp_twitter_url']) && !empty($_SESSION['dimensions']['x0']['child'][$company_id]['comp_twitter_url'])) ? $_SESSION['dimensions']['x0']['child'][$company_id]['comp_twitter_url'] : '';


						$output["code"] = 200;
						$output["msg"] = "Record found";						

					endif;
				endif;

				echo json_encode($output,JSON_UNESCAPED_UNICODE);

			break;

			case 'get-company-stats':				
				$search_param = array();
				$startdate = (isset($startdate) && !empty($startdate)) ? $startdate : START_DATE;
				$enddate = (isset($enddate) && !empty($enddate)) ? $enddate: END_DATE;
				$search_param['startdate'] = $startdate;
				$search_param['enddate'] = $enddate;
				if(isset($company_id) && !empty($company_id))
					$search_param["x0"]=$company_id;

				$pending_records = $confab->get_pending_mention_count($search_param);
				$tagged_records = $confab->get_tagged_mention_count($search_param);
				if(!empty($pending_records) || !empty($tagged_records)):
					$output["code"] = 200;					
					$output['payload']=array(
											"pending_records" => number_format($pending_records), 
											"tagged_records" => number_format($tagged_records)
											);					
				else:
					$output["code"] = 204;
					$output["msg"] = "No Record Found";
				endif;
				echo json_encode($output);				
			break;

			case 'get-company-events':

				if(isset($company_id) && !empty($company_id)):

					//Get latest Annoucement
					$ann_filter = array(
										"startdate" => date("d-m-Y",strtotime("-7 days",strtotime($startdate))),
										"enddate" => date("d-m-Y",strtotime($enddate ." 23:59:59")),
										"company_id" => $company_id
										);
					$get_announcement = $confab->get_company_announcement($ann_filter);					

				endif;

				if(isset($get_announcement) && !empty($get_announcement)):
					$announcements = array();
					foreach($get_announcement as $announcement_key => $announcements_info):
						$announcements[] = array(
												"id" => $announcements_info['id'],
												"company_id" => $announcements_info['comp_id'],
												"platform" => $announcements_info['platform'],
												"title" => $announcements_info['title'],
												"description" => $announcements_info['description'],
												"url" => $announcements_info['url'],
												"raw_time" => $announcements_info['raw_time'],
												"post_time" => $announcements_info['post_time'],
												"insert_time" => $announcements_info['insert_time'],
												"post_time_display" => date("d M, Y",$announcements_info['post_time'])
												);
					endforeach;

					$output["code"] = 200;					
					$output['payload']=array(
											"announcements" => $announcements
											);					
				else:
					$output["code"] = 204;
					$output["msg"] = "No Record Found";
				endif;
				echo json_encode($output);	
			break;

			case 'get-mention-volume':
				if(isset($company_id) && !empty($company_id)):
					$search_param = array();
					$startdate = (isset($startdate) && !empty($startdate)) ? $startdate : START_DATE;
					$enddate = (isset($enddate) && !empty($enddate)) ? $enddate: END_DATE;

					$current_date =date_create(date("d-m-Y",strtotime($enddate)));
					$start_date =date_create(date("d-m-Y",strtotime($startdate)));
					$diff = date_diff($current_date,$start_date);


					//Get hourly sentiment count
					$mysqli->join(MENTIONS_DETAILS_EXT." md","m.id=md.m_id","INNER");
					$mysqli->where("m.post_time",array(strtotime($startdate),strtotime($enddate." 23:59:59")),"BETWEEN");	    			
					$mysqli->where("m.status",LIVE_STATUS_CODE);  
	
					$mysqli->where("md.x0",explode(",", $company_id),"IN");

					$mysqli->groupBy("volume_date");
					// $mysqli->groupBy("x1");
					$mysqli->orderBy("volume_date", "asc");

					if($diff->d==0)
						$select_fields = 'FROM_UNIXTIME(m.post_time,"%k") as volume_date, FROM_UNIXTIME(m.post_time,"%h %p") as volume_date_display, count(m.id) as mention_count';
					else
						$select_fields = 'FROM_UNIXTIME(m.post_time,"%d") as volume_date, FROM_UNIXTIME(m.post_time,"%d %b") as volume_date_display, count(m.id) as mention_count';

					$mysqli->setQueryOption("DISTINCT");
					$mention_volume = $mysqli->get(MENTIONS." as m",null,$select_fields);
					// echo $mysqli->getLastQuery();
					$records = array();
					if(count($mention_volume)):
						foreach($mention_volume as $volume_key => $volume_info):

							$volume_date = (int) $volume_info['volume_date'];
							$records["volume"][$volume_date]["volume_date_display"] = $volume_info["volume_date_display"];
							$records["volume"][$volume_date]['mention_count'] = $volume_info["mention_count"];
							$records["volume"][$volume_date]["cmp"] = 0;

						endforeach;
					endif;




					$mysqli->where("x0_id",explode(",", $company_id),"IN");
					$mysqli->where("cmp_time",array(strtotime($startdate),strtotime($enddate." 23:59:59")),"BETWEEN");	    	
					$mysqli->groupBy("volume_date");
					$mysqli->orderBy("volume_date", "asc");

					if($diff->d==0):
						$selectFields = 'FROM_UNIXTIME(cmp_time,"%k") as volume_date, FROM_UNIXTIME(cmp_time,"%h %p") as volume_date_display, cmp';
					else:
						$selectFields = 'FROM_UNIXTIME(cmp_time,"%d") as volume_date, FROM_UNIXTIME(cmp_time,"%d %b") as volume_date_display, cmp';
						$mysqli->where("start_close_price_flag",2);
					endif;

					$mysqli->setQueryOption("DISTINCT");
					$stock_volume = $mysqli->get(STOCK_CMP." as m",null,$selectFields);
					// echo $mysqli->getLastQuery();
					if(count($stock_volume)):
						$data["stock_hourly_volume"] = array();
						foreach($stock_volume as $volume => $volume_info):

							$volume_date = (int) $volume_info['volume_date'];

							// $data["stock_hourly_volume"][$volume_date]["volume_date_display"] = $volume_info["volume_date_display"];
							// $data["stock_hourly_volume"][$volume_date]["count"] = $volume_info["cmp"];
							$records["volume"][$volume_date]["volume_date_display"] = $volume_info["volume_date_display"];
							$records["volume"][$volume_date]["cmp"] = $volume_info["cmp"];

							$records["volume"][$volume_date]["mention_count"] = (!isset($records["volume"][$volume_date]["mention_count"])) ? 0 : $records["volume"][$volume_date]["mention_count"];

						endforeach;
					endif;	
					


				endif;

				if(isset($records) && !empty($records)):
						$output["code"] = 200;					
						$output['payload']=$records;				
				else:
					$output["code"] = 204;
					$output["msg"] = "No Record Found";
				endif;
					echo json_encode($output);	

			break;

			//Get blog, Forun posts
			case 'get-blog-posts':
				//Social Media Platform
				$blog_forum_platforms = array();
				if(isset($_SESSION["site_list"][2])):
					foreach($_SESSION["site_list"][2] as $site_key => $site_info):
						$blog_forum_platforms[] = $site_info['sitename'];
					endforeach;
				endif;	

				if(isset($_SESSION["site_list"][4])):
					foreach($_SESSION["site_list"][4] as $site_key => $site_info):
						$blog_forum_platforms[] = $site_info['sitename'];
					endforeach;
				endif;		

				$startdate = (isset($startdate) && !empty($startdate)) ? $startdate : START_DATE;
				$enddate = (isset($enddate) && !empty($enddate)) ? $enddate: END_DATE;

				$search_param = array(
										"sort_by" => "m.post_time", 
										"sort_order"=>"DESC",
										"limit"=>"0,50",
										"platform"=>$blog_forum_platforms,
										"startdate" => $startdate,
										"enddate" => $enddate
									);

				if(isset($company_id) && !empty($company_id))
					$search_param['x0'] = $company_id;
				

				if(isset($unique) && !empty($unique)):
					$search_param["group_by"] = 1;
					$search_param["group_by_column_name"] = "metaphone";

				endif;

				$blog_forum_mentions = $confab->get_site_specific_mentions($search_param);

				// print_r($blog_forum_mentions);

				
				if(count($blog_forum_mentions) || count($blog_forum_mentions)):
					$output["code"] = 200;					
					$output['payload']=$blog_forum_mentions["payload"];
					$output["calc_found_rows"] = $blog_forum_mentions["calc_found_rows"];					
				else:
					$output["code"] = 204;
					$output["msg"] = "No Record Found";
				endif;				
				echo json_encode($output);
			break;	


			case 'get-mention-type-volume':

					$startdate = (isset($startdate) && !empty($startdate)) ? $startdate : START_DATE;
					$enddate = (isset($enddate) && !empty($enddate)) ? $enddate: END_DATE;

					if(isset($company_id) && !empty($company_id))
						$mysqli->where("md.x0",explode(",", $company_id),"IN");


					//Get hourly sentiment count
					$mysqli->join(MENTIONS_DETAILS_EXT." md","m.id=md.m_id","INNER");
					$mysqli->where("m.post_time",array(strtotime($startdate),strtotime($enddate." 23:59:59")),"BETWEEN");	    			
					$mysqli->where("m.status",LIVE_STATUS_CODE);  
	
					$mysqli->groupBy("x3");
				
					$mention_volume = $mysqli->get(MENTIONS." as m",null,"count(*) as cnt, x3");
					// echo $mysqli->getLastQuery();
					$records = array();
					if(count($mention_volume)):
						foreach($mention_volume as $volume_key => $volume_info):
							$records[$volume_info['x3']] = array(
															"x3" => $volume_info['x3'],
															'x3_label' => $_SESSION["dimensions"]["x3"]["child"][$volume_info["x3"]]["dimension_attribute"],
															'mention_count' => $volume_info['cnt']
														); 
							
						endforeach;
					endif;


					if(isset($records) && !empty($records)):
						$output["code"] = 200;					
						$output['payload']['volume']=$records;				
					else:
						$output["code"] = 204;
						$output["msg"] = "No Record Found";
					endif;
						echo json_encode($output);	

			break;


			// Tagging actions
			//Single tagging action
			case 'cbfm-action':		

				$m_data = array();
				$md_data = array();
				$output["hide"] = 1; 
				if(isset($_POST["cfbm_data"]) && !empty($_POST["cfbm_data"])):
					foreach($_POST["cfbm_data"] as $cfbmParam => $cfbmVal):						
						// if(isset($cfbmVal["value"]) && !empty($cfbmVal["value"])):
							$arraykey = str_replace("cfbm_tag_", "", $cfbmVal["name"]);
							//if key = x[0-9]  then this field belong to mention details tbl else mention tbl
							if(preg_match('/x([0-9]+)/', $arraykey))
								$md_data[$arraykey] = trim($cfbmVal["value"]);	
							else
								$m_data[$arraykey] = trim($cfbmVal["value"]);	
						// endif;	
					endforeach;

					$m_data['status'] = $takeaction;
					$m_data['tagged_time'] = time();					
					$m_data['tagged_by'] = $_SESSION["email"];			

					if(isset($m_data['cfbm-mention-checkbox']) && !empty($m_data['cfbm-mention-checkbox']))
						unset($m_data['cfbm-mention-checkbox']);
					

					if(isset($m_data['author']) && !empty($m_data['author']))
						unset($m_data['author']);

					if(isset($m_data['post_time']) && !empty($m_data['post_time']))
						unset($m_data['post_time']);
					
					// if(isset($m_data['target_price']) && !empty($m_data['target_price'])):
					if(isset($m_data['target_price'])):
						$md_data['target_price'] = trim($m_data['target_price']);
						unset($m_data['target_price']);
					endif;


					// if(isset($m_data['reco_by']) && !empty($m_data['reco_by'])):
					if(isset($m_data['reco_by'])):
						$md_data['reco_by'] = trim($m_data['reco_by']);
						unset($m_data['reco_by']);
					endif;


					if(isset($m_data['custom_reco_by']) && !empty($m_data['custom_reco_by'])):
						$md_data['reco_by'] = trim($m_data['custom_reco_by']);

						$add_analyst = array("analyst_name"=>trim($m_data['custom_reco_by']));
						$mysqli->insert(ANALYSTS,$add_analyst);
						
						$_SESSION["analyst_list"][strtolower(trim($m_data['custom_reco_by']))] = array(
																					"analyst_name"=>trim($m_data['custom_reco_by']));


						
					endif;

					unset($m_data['custom_reco_by']);

					$tbl_mentions = MENTIONS;
					$tbl_mentions_detail = MENTIONS_DETAILS;
					$tbl_mentions_detail_ext = MENTIONS_DETAILS_EXT;
					$tbl_mentions_log = MENTION_LOG;
					
					if(isset($cfbToken) && in_array($cfbToken, explode(",", ECONOMY_LINKS))):
						$tbl_mentions = ECONOMICS_MENTIONS;
						$tbl_mentions_detail = ECONOMICS_MENTIONS_DETAILS;					
						$tbl_mentions_detail_ext = ECONOMICS_MENTIONS_DETAILS_EXT;					
						$tbl_mentions_log = ECONOMICS_MENTIONS_LOG;					
					endif;

					/*** Mention Log***/
					$is_logged_param=$mentions_log_data=$data_mention=$prev_log_array=$merge_data=array();
					
					$is_logged_param=array( 
											"mention_id" => $m_id,
											"tbl_mentions_log" => $tbl_mentions_log,
											"cfbToken" => $cfbToken,
											"startdate" => $_SESSION["startdate"]
										  );
					
					$mentions_log_data=$confab->get_mention_log($is_logged_param);
					if(isset($mentions_log_data) && empty($mentions_log_data)){
						
						if($tbl_mentions_log == ECONOMICS_MENTIONS_LOG)
							$data_mention=$confab->get_economy_mentions($is_logged_param);
						else
							$data_mention=$confab->get_mentions($is_logged_param);	

						$prev_log_array= array(
										"mention_id" => $m_id,
										"action" => 0,
										"action_by" => "",
										"mention" => json_encode($data_mention),
										"insert_time" => strtotime("now")
									);
						
						$prev_mention_log=$mysqli->insert($tbl_mentions_log,$prev_log_array);
					}	

					
					$merge_data = array_merge($m_data, $md_data); //merge m_data and md_data
					$merge_data['mention_id']= $m_id;
					$merge_data['update_time']= time();
					
					$new_log_array=array();
					$new_log_array= array(
										"mention_id" => $m_id,
										"action" => $takeaction,
										"action_by" => $_SESSION["email"],
										"mention" => json_encode($merge_data),
										"insert_time" => strtotime("now")
									);
					$new_mention_log=$mysqli->insert($tbl_mentions_log,$new_log_array);	
					/****End Mention Log **/

					$mysqli->where("id",$m_id);
					$m_update = $mysqli->update($tbl_mentions,$m_data,1);				
					$output["m_update"] = $m_update;
					$md_data["update_time"] = time();
					

					//if mention sent to live
					if($takeaction==LIVE_STATUS_CODE):	

						$mysqli->where("m_id",$m_id);
						$checkMD_id = $mysqli->getOne($tbl_mentions_detail_ext);		
						//Check mention id already exist in mention tbl 
						if(count($checkMD_id)):
							//update mention detail tbl
							$mysqli->where("m_id",$m_id);
							$md_update =  $mysqli->update($tbl_mentions_detail_ext,$md_data);				
							// echo $mysqli->getLastQuery();
							$output["tmpAction"] = "Update";
							$output["md_update"] = $m_id;
							$output["hide"] = 0; 
						else:
							//add new entry in mention details tbl
							$md_data["m_id"] = $m_id;
							$md_data["insert_time"] = time();	
							$md_data["tagged_time"] = $md_data["insert_time"];
							$mysqli->setQueryOption("IGNORE");					
							$md_update =  $mysqli->insert($tbl_mentions_detail_ext,$md_data);				
							$output["md_update"] = $md_update;
						endif;						

					else:
						//mention in internal console
						$mysqli->where("m_id",$m_id);
						$md_update =  $mysqli->update($tbl_mentions_detail,$md_data,1);					
						$output["md_update"] = $md_update;
					endif;

					if(($m_update) && ($md_update)):
						$output["code"] = 200;
						$output["status"] = "Updated Successfully";
					else:
						$output["code"] = 400;
						$output["status"] = "Error while processing request.";
					endif;
					$output["m_id"] = $m_id;

					echo json_encode($output);
				endif;
				

			break;

			case 'cbfm-ignore-and-reset':
				if(isset($m_id) && !empty($m_id)):
					$m_data['status'] = $takeaction;
					$m_data['tagged_by'] = $_SESSION["email"];
					
					$tbl_mentions = MENTIONS;
					$tbl_mentions_detail = MENTIONS_DETAILS_EXT;
					$tbl_mentions_log = MENTION_LOG;
					if(isset($cfbToken) && in_array($cfbToken, explode(",", ECONOMY_LINKS))):
						$tbl_mentions = ECONOMICS_MENTIONS;
						$tbl_mentions_detail = ECONOMICS_MENTIONS_DETAILS_EXT;
						$tbl_mentions_log = ECONOMICS_MENTIONS_LOG;					
					elseif(isset($cfbToken) && $cfbToken=="epaper"):
						$tbl_mentions = EPAPER_MENTIONS;
						$tbl_mentions_detail = EPAPER_MENTIONS_DETAIL_EXT;	
						$tbl_mentions_log = EPAPER_MENTIONS_LOG;
					endif;


					/*** Mention Log***/
					$is_logged_param=$mentions_log_data=$data_mention=$prev_log_array=$merge_data=array();
					
					$is_logged_param=array( "mention_id" => $m_id,
											"tbl_mentions_log" => $tbl_mentions_log,
											"cfbToken" => $cfbToken,
											"startdate" => $_SESSION["startdate"]
										   );
					
					$mentions_log_data=$confab->get_mention_log($is_logged_param);
					
					if(isset($mentions_log_data) && empty($mentions_log_data)){
						
						if($tbl_mentions_log == ECONOMICS_MENTIONS_LOG)
							$data_mention=$confab->get_economy_mentions($is_logged_param);
						else if ($tbl_mentions_log == EPAPER_MENTIONS_LOG)
							$data_mention=$confab->get_epaper_mentions($is_logged_param);
						else
							$data_mention=$confab->get_mentions($is_logged_param);

						$prev_log_array= array(
										"mention_id" => $m_id,
										"action" => 0,
										"action_by" => "",
										"mention" => json_encode($data_mention),
										"insert_time" => strtotime("now")
									);
						$prev_mention_log=$mysqli->insert($tbl_mentions_log,$prev_log_array);
					}	
					

					//$merge_data = array_merge($m_data, $md_data); //merge m_data and md_data
					$merge_data = $m_data; //merge m_data and md_data
					$merge_data['update_time']= time();	
					$merge_data['mention_id']= $m_id;
					
					$new_log_array=array();
					$new_log_array= array(
										"mention_id" => $m_id,
										"action" => $takeaction,
										"action_by" => $_SESSION["email"],
										"mention" => json_encode($merge_data),
										"insert_time" => strtotime("now")
									);
					$new_mention_log=$mysqli->insert($tbl_mentions_log,$new_log_array);	

					/****End Mention Log **/


					// $confab->printdata($m_data);
					$mysqli->where("id",$m_id);
					$m_update =  $mysqli->update($tbl_mentions,$m_data,1);		
					// echo $mysqli->getLastQuery();
								
					$output["m_update"] = $m_update;
					$output["m_id"] = $m_id;

					$output["hide"] = (in_array($cfbToken, array("ignored","ignored-economy"))) ? 1 : 0; 

					//Delete mention detail from external tbl
					if($takeaction==IGNORE_STATUS_CODE || in_array($takeaction, explode(",", INT_PENDING_STATUS_CODE))):
						$mysqli->where("m_id",$m_id);
						$deleted = $mysqli->delete($tbl_mentions_detail,1);		
						if($deleted):
							$output["info"] = "Mention details deleted from external tbl.";
							$output["hide"] = 1;
						endif;
						
					endif;

					$output["code"] = 200;
					$output["takeaction"] = $takeaction;
					$output["status"] = "Updated Successfully";
					echo json_encode($output);

				endif;

			break;

			case 'cbfm-common-tagging':
				if(isset($m_ids) && !empty($m_ids)):


					if(isset($_POST["cfbm_data"]) && !empty($_POST["cfbm_data"])):

						$m_data = array();
						$md_data = array();

						foreach($_POST["cfbm_data"] as $cfbmParam => $cfbmVal):
							
							if(isset($cfbmVal["value"]) && !empty($cfbmVal["value"])):
								$arraykey = str_replace("cfbm_tag_", "", $cfbmVal["name"]);
								
								//if key = x[0-9]  then this field belong to mention details tbl else mention tbl
								if(preg_match('/x([0-9]+)/', $arraykey))
									$md_data[$arraykey] = $cfbmVal["value"];	
								else
									$md_data[$arraykey] = $cfbmVal["value"];	

							endif;	
						endforeach;

						$m_data['status'] = $takeaction;
						$m_data['tagged_time'] = time();
						$m_data['tagged_by'] = $_SESSION["email"];

						// $confab->printdata($m_data);
						// $confab->printdata($md_data);
						// exit;

						if(isset($m_data['author']) && !empty($m_data['author']))
							unset($m_data['author']);

						if(isset($m_data['post_time']) && !empty($m_data['post_time']))
							unset($m_data['post_time']);

						if(isset($md_data['cfbm-select-all']) && !empty($md_data['cfbm-select-all']))
							unset($md_data['cfbm-select-all']);
						
						if(isset($m_data['target_price']) && !empty($m_data['target_price'])):
							$md_data['target_price'] = $m_data['target_price'];
							unset($m_data['target_price']);
						endif;
						

						if(isset($m_data['reco_by']) && !empty($m_data['reco_by'])):
							$md_data['reco_by'] = $m_data['reco_by'];
							unset($m_data['reco_by']);
						endif;
					


						if(isset($md_data['custom_reco_by']) && !empty($md_data['custom_reco_by'])):
							$md_data['reco_by'] = trim($md_data['custom_reco_by']);

							$add_analyst = array("analyst_name"=>trim($md_data['custom_reco_by']));
							$mysqli->insert(ANALYSTS,$add_analyst);
							
							$_SESSION["analyst_list"][strtolower(trim($md_data['custom_reco_by']))] = array(
																					"analyst_name"=>trim($md_data['custom_reco_by']));						
						endif;

						unset($md_data['custom_reco_by']);

						// echo "Rakesh";
						// exit;

						$md_data["update_time"] = time();

						// $confab->printdata($md_data);
						// exit;
						
						// exit;
						$tbl_mentions = MENTIONS;
						$tbl_mentions_detail = MENTIONS_DETAILS;
						$tbl_mentions_detail_ext = MENTIONS_DETAILS_EXT;
						$tbl_mentions_log = MENTION_LOG;
						
						if(isset($cfbToken) && in_array($cfbToken, explode(",", ECONOMY_LINKS))):
							$tbl_mentions = ECONOMICS_MENTIONS;
							$tbl_mentions_detail = ECONOMICS_MENTIONS_DETAILS;					
							$tbl_mentions_detail_ext = ECONOMICS_MENTIONS_DETAILS_EXT;
							$tbl_mentions_log = ECONOMICS_MENTIONS_LOG;					
						endif;


						/*** Mention Log***/
						foreach($m_ids as $mKeyID => $mValueID):
							$is_logged_param=$mentions_log_data=$data_mention=$prev_log_array=$merge_data=array();
							
							$is_logged_param=array( "mention_id" => $mValueID,
													"tbl_mentions_log" => $tbl_mentions_log,
													"cfbToken" => $cfbToken,
													"startdate" => $_SESSION["startdate"]
												  );
							
							$mentions_log_data=$confab->get_mention_log($is_logged_param);
							
							if(isset($mentions_log_data) && empty($mentions_log_data)){
								
								if($tbl_mentions_log == ECONOMICS_MENTIONS_LOG)
									$data_mention=$confab->get_economy_mentions($is_logged_param);
								else
									$data_mention=$confab->get_mentions($is_logged_param);	

								$prev_log_array= array(
												"mention_id" => $mValueID,
												"action" => 0,
												"action_by" => "",
												"mention" => json_encode($data_mention),
												"insert_time" => strtotime("now")
											);
								$prev_mention_log=$mysqli->insert($tbl_mentions_log,$prev_log_array);
							}	
							
							$mention_info=$mention_info_array=array();
								
							if($tbl_mentions_log == ECONOMICS_MENTIONS_LOG)
								$mention_info=$confab->get_economy_mentions($is_logged_param);
							else
								$mention_info=$confab->get_mentions($is_logged_param);	
							
							$m_status="";
							$m_status=(isset($mention_info['payload'][0]["status"]) && !empty($mention_info['payload'][0]["status"])) ? $mention_info['payload'][0]["status"] : 0;
							$mention_info_array = array (
													"title" => $mention_info['payload'][0]["title"],
													"description" => $mention_info['payload'][0]["description"],
													"url" => $mention_info['payload'][0]["url"],
													"platform" => $mention_info['payload'][0]["platform"],
													"author" => $mention_info['payload'][0]["author"],
													"status" => $m_status,
													"tagged_time" => strtotime("now"),
													"tagged_by" => $_SESSION["email"]
												  );
							
							$merge_data = array_merge($mention_info_array, $md_data); //merge m_data and md_data
							$merge_data['update_time']= time();
							
							$new_log_array=array();
							$new_log_array= array(
												"mention_id" => $mValueID,
												"action" => $takeaction,
												"action_by" => $_SESSION["email"],
												"mention" => json_encode($merge_data),
												"insert_time" => strtotime("now")
											);
							$new_mention_log=$mysqli->insert($tbl_mentions_log,$new_log_array);	
							
						endforeach;
						/****End Mention Log **/

						if($takeaction==LIVE_STATUS_CODE):	
								$md_data["insert_time"] = time();
								$md_data["tagged_time"] = time();
							foreach($m_ids as $mKey => $mValue):
								$md_data["m_id"] = $mValue;								
								$md_update =  $mysqli->insert($tbl_mentions_detail_ext,$md_data);
								// echo $mysqli->getLastQuery();
							endforeach;
						else:
							$mysqli->where("m_id",$m_ids, "IN");
							$md_update =  $mysqli->update($tbl_mentions_detail,$md_data);	
						endif;


						$mysqli->where("id",$m_ids, "IN");
						$m_update =  $mysqli->update($tbl_mentions,$m_data);	
						// echo $mysqli->getLastQuery();			
						$output["m_update"] = $m_update;
						

						if(($m_update) && ($md_update)):
							$output["code"] = 200;
							$output["status"] = "Updated Successfully";
						else:
							$output["code"] = 400;
							$output["status"] = "Error while processing request.";
						endif;
						$output["m_ids"] = $m_ids;

						echo json_encode($output);

					endif;  //check cfbm_data exist


				endif; // m_ids condition end
			break;

			// case 'cbfm-commom-ignore':
			case 'cbfm-commom-ignore-and-reset':
				if(isset($m_ids) && !empty($m_ids)):
					$m_data['status'] = $takeaction;
					$m_data['tagged_by'] = $_SESSION["email"];
					
					$tbl_mentions = MENTIONS;
					$tbl_mentions_detail = MENTIONS_DETAILS;
					$tbl_mentions_detail_ext = MENTIONS_DETAILS_EXT;
					$tbl_mentions_log = MENTION_LOG;					
					if(isset($cfbToken) && in_array($cfbToken, explode(",", ECONOMY_LINKS))):
						$tbl_mentions = ECONOMICS_MENTIONS;
						$tbl_mentions_detail = ECONOMICS_MENTIONS_DETAILS;					
						$tbl_mentions_detail_ext = ECONOMICS_MENTIONS_DETAILS_EXT;
						$tbl_mentions_log = ECONOMICS_MENTIONS_LOG;					
					endif;

					/*** Mention Log***/
					$is_logged_param=$mentions_log_data=$data_mention=$prev_log_array=$merge_data=array();
					
					foreach ($m_ids as $m_key => $m_value) {
						
						$is_logged_param=$mentions_log_data=$data_mention=$prev_log_array=$merge_data=array();

						$is_logged_param=array( "mention_id" => $m_value,
												"tbl_mentions_log" => $tbl_mentions_log,
												"cfbToken" => $cfbToken,
												"startdate" => $_SESSION["startdate"]
											  );
						
						$mentions_log_data=$confab->get_mention_log($is_logged_param);
						
						if(isset($mentions_log_data) && empty($mentions_log_data)){
								
							if($tbl_mentions_log == ECONOMICS_MENTIONS_LOG)
								$data_mention=$confab->get_economy_mentions($is_logged_param);
							else
								$data_mention=$confab->get_mentions($is_logged_param);
							
							$prev_log_array= array(
											"mention_id" => $m_value,
											"action" => 0,
											"action_by" => "",
											"mention" => json_encode($data_mention),
											"insert_time" => strtotime("now")
										);
							$prev_mention_log=$mysqli->insert($tbl_mentions_log,$prev_log_array);
						}

						//$merge_data = array_merge($m_data, $md_data); //merge m_data and md_data
						$merge_data = $m_data; //merge m_data and md_data
						$merge_data['update_time']= time();
						$merge_data['mention_id']= $m_value;
						
						$new_log_array=array();
						$new_log_array= array(
											"mention_id" => $m_value,
											"action" => $takeaction,
											"action_by" => $_SESSION["email"],
											"mention" => json_encode($merge_data),
											"insert_time" => strtotime("now")
										);
						$new_mention_log=$mysqli->insert($tbl_mentions_log,$new_log_array);	
						
					} //end foreach for every mention id

					/****End Mention Log **/

					$mysqli->where("id",$m_ids, "IN");
					$m_update =  $mysqli->update($tbl_mentions,$m_data);					
					$output["m_update"] = $m_update;				


					//Delete mention detail from external tbl
					if($takeaction==IGNORE_STATUS_CODE || in_array($takeaction, explode(",", INT_PENDING_STATUS_CODE))):
						$mysqli->where("m_id",$m_ids,"IN");
						$deleted = $mysqli->delete($tbl_mentions_detail_ext,count($m_ids));		
						if($deleted):
							$output["info"] = "Mention details deleted from external tbl.";
							$output["hide"] = 1;
						endif;						
					endif;


					if(($m_update)):
						$output["code"] = 200;
						$output["status"] = "Ignored Successfully";
					else:
						$output["code"] = 400;
						$output["status"] = "Error while processing request.";
					endif;
					$output["m_ids"] = $m_ids;

					echo json_encode($output);


				endif;

			break;

			case 'get_influencers':
                $result=$output="";
                
                $startdate = (isset($startdate) && !empty($startdate)) ? $startdate : START_DATE;
                $enddate = (isset($enddate) && !empty($enddate)) ? $enddate: END_DATE;

                $param_array=array(
                        "company" => $company,
                        "startdate" => $startdate,
                        "enddate" => $enddate,
                        "limit"   => INFLUENCERS_COUNT
                        );
                $result=$confab->get_influencers($param_array);
                
                if(count($result) > 0 ){
                    $output["code"] = 200;
                    $output["status"] = "get influencers data Successfully";
                    $output["payload"]['data'] = $result;
                }else{
                    $output["code"] = 400;
                    $output["status"] = "No response";
                    $output["payload"]['data'] = "";
                }

                echo json_encode($output);
            break;


            case 'get-analyst-list': //from db
                $result=$output="";
                
                $startdate = (isset($startdate) && !empty($startdate)) ? $startdate : START_DATE;
                $enddate = (isset($enddate) && !empty($enddate)) ? $enddate: END_DATE;

                $param_array=array(
                			'analyst_name'=>$analyst_name
                        );
                $result=$confab->get_analyst_list($param_array);
                
                if(count($result) > 0 ){
                    $output["code"] = 200;
                    $output["status"] = "get analysts data Successfully";
                    $output["payload"]['data'] = $result;
                }else{
                    $output["code"] = 400;
                    $output["status"] = "No response";
                    $output["payload"]['data'] = "";
                }

                echo json_encode($output);
            break;

            case 'get-analysts-list': //from Session
                $result=$output="";
                
                $startdate = (isset($startdate) && !empty($startdate)) ? $startdate : START_DATE;
                $enddate = (isset($enddate) && !empty($enddate)) ? $enddate: END_DATE;
                $search_data=array();
                if(isset($analyst_name) && !empty($analyst_name)){
	                
	                foreach ($_SESSION['analyst_list'] as $key => $value) {
	                	if(stristr($key,$analyst_name)){
	                		$search_data[$key]=$_SESSION['analyst_list'][$key];
	                	}
	                }
            	}else{
            		$search_data=$_SESSION['analyst_list'];
            		$search_data = array_slice($search_data, 0, 10,true);
            	}
                
                if(count($result) > 0 ){
                    $output["code"] = 200;
                    $output["status"] = "get analysts data Successfully";
                    $output["payload"]['data'] = $search_data;
                }else{
                    $output["code"] = 400;
                    $output["status"] = "No response";
                    $output["payload"]['data'] = "";
                }

                echo json_encode($output);
            break;

            case 'get-company-list': //from Session
                $result=$output="";
                
                $startdate = (isset($startdate) && !empty($startdate)) ? $startdate : START_DATE;
                $enddate = (isset($enddate) && !empty($enddate)) ? $enddate: END_DATE;
                $search_data=array();
                if(isset($company_name) && !empty($company_name)){
	                foreach ($_SESSION['dimensions']['x0']['child'] as $key => $value) {
						if(stristr($value['dimension_attribute'],$company_name) || stristr($value['extra'],$company_name)){
	                		$search_data[$value['dimension_attribute']]=$_SESSION['dimensions']['x0']['child'][$key];
	                	}
	                }
            	}else{
            		$search_data=$_SESSION['dimensions']['x0']['child'];
            		$search_data = array_slice($search_data, 0, 10,true);
            	}
                
                if(count($result) > 0 ){
                    $output["code"] = 200;
                    $output["status"] = "get companys data Successfully";
                    $output["payload"]['data'] = $search_data;
                }else{
                    $output["code"] = 400;
                    $output["status"] = "No response";
                    $output["payload"]['data'] = "";
                }

                echo json_encode($output);
            break;


            case 'get-analysts-recos':
            	echo "not using";exit;
				//update cfbtoken session value.
				if(isset($cfbToken) && !empty($cfbToken))
					$_SESSION['cfbToken'] = $cfbToken;

				$_SESSION['selectedDimension'] = array();
				
				if(isset($reco_by) && !empty($reco_by)){
					$filter_param['reco_by']=$reco_by;
				}
				

				$_SESSION["startdate"] = (isset($startdate) && !empty($startdate)) ? $startdate : $_SESSION["startdate"];
				$_SESSION["enddate"] = (isset($enddate) && !empty($enddate)) ? $enddate : $_SESSION["enddate"];

				$filter_param["limit"] = (isset($limit) && !empty($limit)) ? $limit : null;

				$filter_param["startdate"] = $_SESSION["startdate"];
				$filter_param["enddate"] = $_SESSION["enddate"];

				//today's date start from 15:30:01 of last treding date.
				//get last treding date.
				$current_date =date_create(date("d-m-Y",time()));
				$start_date =date_create(date("d-m-Y",strtotime($filter_param["startdate"])));
				$diff = date_diff($current_date,$start_date);
				$startDateCount = $diff->d + 1;
				$get_start_date = $confab->getPreviousDate($startDateCount);

				//Change startdate value
				$filter_param["startdate"] = (isset($get_start_date) && !empty($get_start_date)) ? date("d-m-Y",$get_start_date) : $_SESSION["startdate"];

				//$startdate = (isset($startdate) && !empty($startdate)) ? $startdate : START_DATE;
                //$enddate = (isset($enddate) && !empty($enddate)) ? $enddate: END_DATE;

				//Change startdate value
				//$filter_param["startdate"] = $startdate;
				//$filter_param["enddate"] = $enddate;
				
				$_SESSION['filter_param'] = $filter_param;
				
				$records = $confab->get_reco($filter_param);

				if(isset($records["payload"]) && count($records["payload"])):
					$output["code"] = 200;
					$output["payload"] = $records["payload"];
					$output["calc_found_rows"] = $records["calc_found_rows"];
				else:
					$output["code"] = 204;
					$output["msg"] = "No Record Found";

				endif;
				// echo json_encode($output,JSON_UNESCAPED_UNICODE);
				echo json_encode($output,true);

			break;


			case 'get-analysts-recos-php':

				//update cfbtoken session value.
				if(isset($cfbToken) && !empty($cfbToken))
					$_SESSION['cfbToken'] = $cfbToken;

				$_SESSION['selectedDimension'] = array();
				
				if(isset($reco_by) && !empty($reco_by)){
					$filter_param['reco_by']=$reco_by;
				}
				

				$_SESSION["startdate"] = (isset($startdate) && !empty($startdate)) ? $startdate : $_SESSION["startdate"];
				$_SESSION["enddate"] = (isset($enddate) && !empty($enddate)) ? $enddate : $_SESSION["enddate"];

				$filter_param["limit"] = (isset($limit) && !empty($limit)) ? $limit : null;

				$filter_param["startdate"] = $_SESSION["startdate"];
				$filter_param["enddate"] = $_SESSION["enddate"];

				//today's date start from 15:30:01 of last treding date.
				//get last treding date.
				$current_date =date_create(date("d-m-Y",time()));
				$start_date =date_create(date("d-m-Y",strtotime($filter_param["startdate"])));
				$diff = date_diff($current_date,$start_date);
				$startDateCount = $diff->d + 1;
				$get_start_date = $confab->getPreviousDate($startDateCount);

				//Change startdate value
				$filter_param["startdate"] = (isset($get_start_date) && !empty($get_start_date)) ? date("d-m-Y",$get_start_date) : $_SESSION["startdate"];

				$filter_param["orderBy"]="DESC";
				$_SESSION['filter_param'] = $filter_param;
				
				//print_r($filter_param);exit;
				$records = $confab->get_reco($filter_param);

				if(isset($records["payload"]) && count($records["payload"])):

					/*change js to php*/
					$tpl_content = file_get_contents("account/nseit/templates/partial.reco-box-layout.tpl");

		            $rowCounter = $buyCallCounter = $sellCallCounter = 1;
		            $buyCalls = $sellCalls = $totalCalls="";
		            $company_array = $display_none = array();	
					
					foreach ($records["payload"] as $key => $value) {
						$optHtml = '';
                		$optHtml .= $tpl_content;
                		
                		//Replace placeholder with actual data
		                $authorname = ($value['author'] !="" && $value['author']!=0) ? $value['author'] : "Anonymous";
		                $optHtml = str_replace("STR_MENTION_ID",$value['mention_id'],$optHtml);       
		                $mbadge = $value['x1_label']=='Neutral'?'warning': $value['x1_label']=='Positive'?'success':'danger';
		                $recoColor = $value['x2_label']=='Hold'?'warning': $value['x2_label']=='Buy'?'success':'danger';
		                $cmp_color = $value['x2_label']=='Hold'?'warning': $value['x2_label']=='Buy'?'success':'danger';
		                $reco_call_class = $value['x2_label']=='Hold'?'reco-hold-calls':$value['x2_label']=='Buy'?'reco-buy-calls':'reco-sell-calls';

		                $optHtml = str_replace("RECO_CALL_CLASS", $reco_call_class,$optHtml); 

		                $optHtml = str_replace("STR_MENTION_BADGE", $mbadge,$optHtml); 
		                $optHtml = str_replace("STR_MENTION_RECO_BADGE", $recoColor,$optHtml); 
		                $optHtml = str_replace("STR_SR_NO",$rowCounter,$optHtml);
		                $optHtml = str_replace("STR_MENTION_TRACK_DATE",$value['insert_time_format'],$optHtml);
		                $optHtml = str_replace("STR_MENTION_POSTDATE_WITH_TIME",$value['mention_date_format_with_time'],$optHtml);               
		                $optHtml = str_replace("STR_MENTION_AUTHOR",$authorname,$optHtml);
		                $optHtml = str_replace("STR_X0_LABEL",$value['x0_label'],$optHtml);  //for show company uncomment this
		                $optHtml = str_replace("STR_X2_LABEL",$value['x2_label'],$optHtml);
		                $optHtml = str_replace("STR_X5_LABEL",$value['x5_label'],$optHtml);
		                
		                $target_price = ($value['target_price']!="") ? $value['target_price'] : "-";
		                $optHtml = str_replace("STR_TARGET_PRICE",$target_price,$optHtml);
		                $optHtml = str_replace("STR_RECO_BY","",$optHtml);
		                $optHtml = str_replace("STR_COMPANY_SYMBOL",$value['company_symbol'],$optHtml);
		                $variationColor = ($value['variation'] > 0) ? 'success':'danger';
		                $optHtml = str_replace("STR_PRICE_VARIATION",round($value['variation'],2),$optHtml);
		                $optHtml = str_replace("STR_CLOSE_VARIATION",round($value['closing_variation'],2),$optHtml);
		                $optHtml = str_replace("STR_CURRENT_PRICE",round($value['current_price'],2),$optHtml);
		                $optHtml = str_replace("STR_CMP_COLOR", $cmp_color,$optHtml); 
		                $optHtml = str_replace("STR_VARIATION_COLOR",$variationColor,$optHtml);
		                $optHtml = str_replace("STR_MENTION_COUNTER",$key,$optHtml);	

		                $company_pic=$comp_pic=""; //comp_list_json[value.x0].comp_logo
		                $comp_pic=$_SESSION['dimensions']['x0']['child'][$value['x0']]['comp_logo'];	
		                $company_pic =($comp_pic!="") ? $comp_pic :"holder.js/200x200?random=yes&bg=#888&fg=#888&text=.";
               			$optHtml = str_replace("STR_ANALYST_PROFILE_PIC",$company_pic,$optHtml);
                
               			$platform = '<img src="https://www.google.com/s2/favicons?domain='.$value['platform'].'"> '. $value['platform'];
		                $optHtml = str_replace("STR_MENTION_PLATFORM",$platform,$optHtml);	

		                if(isset($value['announcement']) && !empty($value['announcement'])){
		                    $marquee = '';
		                    $announcement = '<h6 class="marging-left-20 margin-bottom-5 padding-left-20">Related News</h6>';
		                    $announcement +='<ul class="height-100" style="overflow-y: scroll;">';
		                    
		                    foreach ($value['announcement'] as $key_ann => $v) {

		                        $announcement_desc="";	
		                        $announcement_desc= (isset($v['description']) && strlen($v['description']) > 100 ) ? substr($v['description'], 0, 100) : $v['description'] ;	
		                        $announcement .='<li><span class="alert-ts margin-right-5">'.$v['post_date_format'].'</span><a href="'.$v['url'].'" title="'.$v['post_date_format'].': '.$v['description'].'" target="_blank" class="color-info">'.$announcement_desc.'</a></li>';
		                        $marquee .=' <strong>['.$v['post_date_format'].'] </strong> <a href="'.$v['url'].'" title="'.$v['post_date_format'].': '.$v['description'].'" target="_blank" class="">'.$announcement_desc.'</a>';
		                    }
		                    
		                    $announcement .='</ul>';
		                    
		                    $optHtml = str_replace("STR_LATEST_ANNOUNCEMENT",$marquee,$optHtml);
		                }else{
		                    $optHtml = str_replace("STR_LATEST_ANNOUNCEMENT","",$optHtml);

		                }


		                if($value['x2_label']=="Buy" && $buyCallCounter > 20){
		                    $display_none[]=$key;
		                }
		                if($value['x2_label']=="Sell" && $sellCallCounter > 20){
		                    $display_none[]=$key;
		                }

		                if(in_array($value['x0'], $company_array)){ 
		                    $display_none=$key;
		                }else{
		                    if($value['x2_label']=="Buy")                   
		                        $buyCallCounter = $buyCallCounter + 1;  
		                    if($value['x2_label']=="Sell")
		                        $sellCallCounter = $sellCallCounter + 1;
		                }
		                
		                /*if($value['x2_label']=="Buy")                   
		                    $buyCalls .=$optHtml;
		                else if($value['x2_label']=="Sell")
		                    $sellCalls .=$optHtml;   */

		                $totalCalls .= $optHtml;
		                
		                $company_array[]=$value['x0'];
		                $rowCounter++;	
		            
					}


					/*end change js to php*/



					$output["code"] = 200;
					//$output["payload"] = $records["payload"];
					//$output["calc_found_rows"] = $records["calc_found_rows"];
					//$output["payload"]['data'] = $optHtml;
					/*$output["payload"]['buyCalls'] = $buyCalls;
					$output["payload"]['sellCalls'] = $sellCalls;*/
					$output["payload"]['totalCalls'] = $totalCalls;
					
				else:
					$output["code"] = 204;
					$output["msg"] = "No Record Found";

				endif;
				// echo json_encode($output,JSON_UNESCAPED_UNICODE);
				echo json_encode($output,true);

			break;


			case 'get-companys-recos':

				//update cfbtoken session value.
				if(isset($cfbToken) && !empty($cfbToken))
					$_SESSION['cfbToken'] = $cfbToken;

				$_SESSION['selectedDimension'] = array();
				
				if(isset($keygroup) && !empty($keygroup)){
					$filter_param['keygroup']=array($keygroup);
				}
				

				$_SESSION["startdate"] = (isset($startdate) && !empty($startdate)) ? $startdate : $_SESSION["startdate"];
				$_SESSION["enddate"] = (isset($enddate) && !empty($enddate)) ? $enddate : $_SESSION["enddate"];
				$filter_param["limit"] = (isset($limit) && !empty($limit)) ? $limit : INT_MENTION_PER_PAGE;

				$filter_param["startdate"] = $_SESSION["startdate"];
				$filter_param["enddate"] = $_SESSION["enddate"];

				//today's date start from 15:30:01 of last treding date.
				//get last treding date.
				$current_date =date_create(date("d-m-Y",time()));
				$start_date =date_create(date("d-m-Y",strtotime($filter_param["startdate"])));
				$diff = date_diff($current_date,$start_date);
				$startDateCount = $diff->d + 1;
				$get_start_date = $confab->getPreviousDate($startDateCount);

				//Change startdate value
				$filter_param["startdate"] = (isset($get_start_date) && !empty($get_start_date)) ? date("d-m-Y",$get_start_date) : $_SESSION["startdate"];

				//$startdate = (isset($startdate) && !empty($startdate)) ? $startdate : START_DATE;
                //$enddate = (isset($enddate) && !empty($enddate)) ? $enddate: END_DATE;

				//Change startdate value
				//$filter_param["startdate"] = $startdate;
				//$filter_param["enddate"] = $enddate;

				$_SESSION['filter_param'] = $filter_param;
				
				$records = $confab->get_reco($filter_param);

				if(isset($records["payload"]) && count($records["payload"])):
					$output["code"] = 200;
					$output["payload"] = $records["payload"];
					$output["calc_found_rows"] = $records["calc_found_rows"];
				else:
					$output["code"] = 204;
					$output["msg"] = "No Record Found";

				endif;
				// echo json_encode($output,JSON_UNESCAPED_UNICODE);
				echo json_encode($output,true);

			break;

			case 'get-companys-recos-php':

				//update cfbtoken session value.
				if(isset($cfbToken) && !empty($cfbToken))
					$_SESSION['cfbToken'] = $cfbToken;

				$_SESSION['selectedDimension'] = array();
				
				if(isset($keygroup) && !empty($keygroup)){
					$filter_param['keygroup']=array($keygroup);
				}
				

				$_SESSION["startdate"] = (isset($startdate) && !empty($startdate)) ? $startdate : $_SESSION["startdate"];
				$_SESSION["enddate"] = (isset($enddate) && !empty($enddate)) ? $enddate : $_SESSION["enddate"];
				$filter_param["limit"] = (isset($limit) && !empty($limit)) ? $limit : INT_MENTION_PER_PAGE;

				$filter_param["startdate"] = $_SESSION["startdate"];
				$filter_param["enddate"] = $_SESSION["enddate"];

				//today's date start from 15:30:01 of last treding date.
				//get last treding date.
				$current_date =date_create(date("d-m-Y",time()));
				$start_date =date_create(date("d-m-Y",strtotime($filter_param["startdate"])));
				$diff = date_diff($current_date,$start_date);
				$startDateCount = $diff->d + 1;
				$get_start_date = $confab->getPreviousDate($startDateCount);

				//Change startdate value
				$filter_param["startdate"] = (isset($get_start_date) && !empty($get_start_date)) ? date("d-m-Y",$get_start_date) : $_SESSION["startdate"];

				$filter_param["orderBy"]="DESC";
				$_SESSION['filter_param'] = $filter_param;
				
				$records = $confab->get_reco($filter_param);

				if(isset($records["payload"]) && count($records["payload"])):

					/*change js to php*/
					$tpl_content = file_get_contents("account/nseit/templates/partial.reco-box-layout.tpl");
					$rowCounter = $buyCallCounter = $sellCallCounter = 1;
            		$buyCalls = $sellCalls = $totalCalls ="";
            		$company_array = $display_none =array();
            		
            		foreach ($records["payload"] as $key => $value) {
						$optHtml = '';
                		$optHtml .= $tpl_content;

                		//Replace placeholder with actual data
		                $authorname = ($value['author'] !="" && $value['author']!=0) ? $value['author'] : "Anonymous";
		                $optHtml = str_replace("STR_MENTION_ID",$value['mention_id'],$optHtml);
		                $mbadge = $value['x1_label']=='Neutral'?'warning': $value['x1_label']=='Positive'?'success':'danger';
		                $recoColor = $value['x2_label']=='Hold'?'warning': $value['x2_label']=='Buy'?'success':'danger';
		                $cmp_color = $value['x2_label']=='Hold'?'warning': $value['x2_label']=='Buy'?'success':'danger';
		                $reco_call_class = $value['x2_label']=='Hold'?'reco-hold-calls':$value['x2_label']=='Buy'?'reco-buy-calls':'reco-sell-calls';

		                $optHtml = str_replace("RECO_CALL_CLASS", $reco_call_class,$optHtml); 

		                $optHtml = str_replace("STR_MENTION_BADGE", $mbadge,$optHtml); 
		                $optHtml = str_replace("STR_MENTION_RECO_BADGE", $recoColor,$optHtml); 
		                $optHtml = str_replace("STR_SR_NO",$rowCounter,$optHtml);
		                $optHtml = str_replace("STR_MENTION_TRACK_DATE",$value['insert_time_format'],$optHtml);
		                $optHtml = str_replace("STR_MENTION_POSTDATE_WITH_TIME",$value['mention_date_format_with_time'],$optHtml);

		                $optHtml = str_replace("STR_MENTION_AUTHOR",$authorname,$optHtml);
		                $optHtml = str_replace("STR_X0_LABEL",$value['reco_by'],$optHtml);  //for show company uncomment this
		                $optHtml = str_replace("STR_X2_LABEL",$value['x2_label'],$optHtml);
		                $optHtml = str_replace("STR_X5_LABEL",$value['x5_label'],$optHtml);

		                $target_price = ($value['target_price']!="") ? $value['target_price'] : "-";
		                $optHtml = str_replace("STR_TARGET_PRICE",$target_price,$optHtml);
		                $optHtml = str_replace("STR_RECO_BY","",$optHtml);
		                $optHtml = str_replace("STR_COMPANY_SYMBOL",$value['company_symbol'],$optHtml);
		                $variationColor = ($value['variation'] > 0) ? 'success':'danger';
		                $optHtml = str_replace("STR_PRICE_VARIATION",round($value['variation'],2),$optHtml);
		                $optHtml = str_replace("STR_CLOSE_VARIATION",round($value['closing_variation'],2),$optHtml);
		                $optHtml = str_replace("STR_CURRENT_PRICE",round($value['current_price'],2),$optHtml);
		                $optHtml = str_replace("STR_CMP_COLOR", $cmp_color,$optHtml); 
		                $optHtml = str_replace("STR_VARIATION_COLOR",$variationColor,$optHtml);
		                $optHtml = str_replace("STR_MENTION_COUNTER",$key,$optHtml);

		                $analyst_pic=$comp_pic=""; //comp_list_json[value.x0].comp_logo
				        $analyst_pic = ($value['analyst_mc_profile_pic']!="") ? $value['analyst_mc_profile_pic'] : "holder.js/200x200?random=yes&bg=#888&fg=#888&text=.";
		                $optHtml = str_replace("STR_ANALYST_PROFILE_PIC",$analyst_pic,$optHtml);


		                $platform = '<img src="https://www.google.com/s2/favicons?domain='.$value["platform"].'"> '. $value['platform'];
		                $optHtml = str_replace("STR_MENTION_PLATFORM",$platform,$optHtml);

		                if(isset($value['announcement']) && !empty($value['announcement'])){
		                    $marquee = '';
		                    $announcement = '<h6 class="marging-left-20 margin-bottom-5 padding-left-20">Related News</h6>';
		                    $announcement +='<ul class="height-100" style="overflow-y: scroll;">';
		                    
		                    foreach ($value['announcement'] as $key_ann => $v) {

		                        $announcement_desc="";	
		                        $announcement_desc= (isset($v['description']) && strlen($v['description']) > 100 ) ? substr($v['description'], 0, 100) : $v['description'] ;	
		                        $announcement .='<li><span class="alert-ts margin-right-5">'.$v['post_date_format'].'</span><a href="'.$v['url'].'" title="'.$v['post_date_format'].': '.$v['description'].'" target="_blank" class="color-info">'.$announcement_desc.'</a></li>';
		                        $marquee .=' <strong>['.$v['post_date_format'].'] </strong> <a href="'.$v['url'].'" title="'.$v['post_date_format'].': '.$v['description'].'" target="_blank" class="">'.$announcement_desc.'</a>';
		                    }
		                    
		                    $announcement .='</ul>';
		                    
		                    $optHtml = str_replace("STR_LATEST_ANNOUNCEMENT",$marquee,$optHtml);
		                }else{
		                    $optHtml = str_replace("STR_LATEST_ANNOUNCEMENT","",$optHtml);
		                }

		                if($value['x2_label']=="Buy" && $buyCallCounter > 20){
		                    $display_none[]=$key;
		                }
		                if($value['x2_label']=="Sell" && $sellCallCounter > 20){
		                    $display_none[]=$key;
		                }

		                if(in_array($value['x0'], $company_array)){ 
		                    $display_none=$key;
		                }else{
		                    if($value['x2_label']=="Buy")                   
		                        $buyCallCounter = $buyCallCounter + 1;  
		                    if($value['x2_label']=="Sell")
		                        $sellCallCounter = $sellCallCounter + 1;
		                }

		                /*if($value['x2_label']=="Buy")                   
		                    $buyCalls .=$optHtml;
		                else if($value['x2_label']=="Sell")
		                    $sellCalls .=$optHtml;*/   

		                $totalCalls .=$optHtml;   
		                
		                $company_array[]=$value['x0'];
		                $rowCounter++;

                	}		

					/*end change js to php*/



					$output["code"] = 200;
					/*$output["payload"]['all'] = $records["payload"];
					$output["payload"]['buyCalls'] = $buyCalls;
					$output["payload"]['sellCalls'] = $sellCalls;*/
					$output["payload"]['totalCalls'] = $totalCalls;
				else:
					$output["code"] = 204;
					$output["msg"] = "No Record Found";

				endif;
				// echo json_encode($output,JSON_UNESCAPED_UNICODE);
				echo json_encode($output,true);

			break;


			case 'get-top-analysts':
				
				$_SESSION["startdate"] = (isset($startdate) && !empty($startdate)) ? $startdate : $_SESSION["startdate"];
				$_SESSION["enddate"] = (isset($enddate) && !empty($enddate)) ? $enddate : $_SESSION["enddate"];
				$filter_param["startdate"] = $_SESSION["startdate"];
				$filter_param["enddate"] = $_SESSION["enddate"];

				//today's date start from 15:30:01 of last treding date.
				//get last treding date.
				$current_date =date_create(date("d-m-Y",time()));
				$start_date =date_create(date("d-m-Y",strtotime($filter_param["startdate"])));
				$diff = date_diff($current_date,$start_date);
				$startDateCount = $diff->d + 1;
				$get_start_date = $confab->getPreviousDate($startDateCount);

				//Change startdate value
				$filter_param["startdate"] = (isset($get_start_date) && !empty($get_start_date)) ? date("d-m-Y",$get_start_date) : $_SESSION["startdate"];

				$filter_param["startdate"] = (isset($filter_param["startdate"]) && !empty($filter_param["startdate"])) ? strtotime($filter_param["startdate"]." 15:31:00") : strtotime('-1 day',strtotime(START_DATE." 15:31:00"));
        		$filter_param["enddate"] = (isset($filter_param["enddate"]) && !empty($filter_param["enddate"])) ? strtotime($filter_param["enddate"]." 15:30:59"): strtotime(END_DATE. " 15:30:59");

        		$mysqli->join(MENTIONS_DETAILS_EXT." md","m.id=md.m_id","INNER");
        		//$mysqli->join(ANALYSTS." a","a.analyst_name=md.reco_by","INNER");
				$mysqli->where("m.post_time",array($filter_param["startdate"],$filter_param["enddate"]),"BETWEEN");
				
				$mysqli->where("md.reco_by!=''");
				$mysqli->where("m.status",7);
				$mysqli->groupBy("reco_by");
				$mysqli->orderBy("count(m.id)","DESC");
				//$selectFields = 'md.reco_by,count(m.id) as reco_count, a.id, a.analyst_name, a.mc_news_topics, a.mc_profile_pic, a.mc_description';
				$selectFields = 'md.reco_by,count(m.id) as reco_count';
				$limit = (isset($limit) && !empty($limit)) ? $limit : 10;
				$result = $mysqli->get(MENTIONS." m", $limit ,$selectFields);
				$qry=$mysqli->getLastQuery();
				$output = array();
				$output['qry'] = $qry;
			   	$analyst_data=array();
			   	
			   	if(isset($result) && count($result)):
			   		$counter_an=1;
			   		foreach ($result as $key => $value) {

						$analyst_id="";
						$analyst_id=(isset($_SESSION["analyst_list"][strtolower($value['reco_by'])]) && !empty($_SESSION["analyst_list"][strtolower($value['reco_by'])])) ? $_SESSION["analyst_list"][strtolower($value['reco_by'])]['id'] : "";

						$analyst_data[$counter_an]['id']= $analyst_id;
						$analyst_data[$counter_an]['analyst_name']= $value['reco_by'];
						$analyst_data[$counter_an]['reco_count']= $value['reco_count'];

						$analyst_data[$counter_an]['mc_description']=(isset($_SESSION["analyst_list"][strtolower($value['reco_by'])]) && !empty($_SESSION["analyst_list"][strtolower($value['reco_by'])])) ? $_SESSION["analyst_list"][strtolower($value['reco_by'])]['mc_description'] : "";          
						$analyst_data[$counter_an]['mc_profile_pic']=(isset($_SESSION["analyst_list"][strtolower($value['reco_by'])]) && !empty($_SESSION["analyst_list"][strtolower($value['reco_by'])])) ? $_SESSION["analyst_list"][strtolower($value['reco_by'])]['mc_profile_pic'] : "";          
						$analyst_data[$counter_an]['mc_news_topics']=(isset($_SESSION["analyst_list"][strtolower($value['reco_by'])]) && !empty($_SESSION["analyst_list"][strtolower($value['reco_by'])])) ? $_SESSION["analyst_list"][strtolower($value['reco_by'])]['mc_news_topics'] : "";      
						$counter_an++;    
					}

					$output["code"] = 200;
					$output["payload"]['data'] = $analyst_data;
				else:
					$output["code"] = 204;
					$output["msg"] = "No Record Found";
				endif;
				
				echo json_encode($output,true);
				
			break;

			case 'get-top-companies':
				
				$_SESSION["startdate"] = (isset($startdate) && !empty($startdate)) ? $startdate : $_SESSION["startdate"];
				$_SESSION["enddate"] = (isset($enddate) && !empty($enddate)) ? $enddate : $_SESSION["enddate"];
				$filter_param["startdate"] = $_SESSION["startdate"];
				$filter_param["enddate"] = $_SESSION["enddate"];

				//today's date start from 15:30:01 of last treding date.
				//get last treding date.
				$current_date =date_create(date("d-m-Y",time()));
				$start_date =date_create(date("d-m-Y",strtotime($filter_param["startdate"])));
				$diff = date_diff($current_date,$start_date);
				$startDateCount = $diff->d + 1;
				$get_start_date = $confab->getPreviousDate($startDateCount);

				//Change startdate value
				$filter_param["startdate"] = (isset($get_start_date) && !empty($get_start_date)) ? date("d-m-Y",$get_start_date) : $_SESSION["startdate"];

				$filter_param["startdate"] = (isset($filter_param["startdate"]) && !empty($filter_param["startdate"])) ? strtotime($filter_param["startdate"]." 15:31:00") : strtotime('-1 day',strtotime(START_DATE." 15:31:00"));
        		$filter_param["enddate"] = (isset($filter_param["enddate"]) && !empty($filter_param["enddate"])) ? strtotime($filter_param["enddate"]." 15:30:59"): strtotime(END_DATE. " 15:30:59");	


				$mysqli->join(MENTIONS_DETAILS_EXT." md","m.id=md.m_id","INNER");
        		$mysqli->join(DIMENSIONS." d","d.id=md.x0","INNER");
				
				$mysqli->where("m.post_time",array($filter_param["startdate"],$filter_param["enddate"]),"BETWEEN");
				$mysqli->where("md.reco_by!=''");
				$mysqli->where("m.status",7);
				$mysqli->where("m.priority",1);
				$keygroup_in=array('507','508');
				$mysqli->where("md.x2", $keygroup_in,"IN");
				
				$mysqli->groupBy("md.x0");
				$mysqli->orderBy("count(m.id)","DESC");
				$selectFields = 'md.x0 as id,count(m.id) as reco_count';
				$limit = (isset($limit) && !empty($limit)) ? $limit : 10;
			    $result = $mysqli->get(MENTIONS." m", $limit ,$selectFields);
			    $qry_comp=$mysqli->getLastQuery();
			    $comp_chilids=$_SESSION['dimensions']['x0']['child'];
			    $company_data="";
			    $comp_childs=$_SESSION['dimensions']['x0']['child'];
			    

			    
			    
			    $output = array();
				if(isset($result) && count($result)):
					foreach ($result as $key => $value) {      //for a count do one more loop
			    		$company_data[$value['id']]=$comp_childs[$value['id']];
			    	}

					$output["code"] = 200;
					$output["payload"]['data'] = $company_data;
					$output["qry_comp"] = $qry_comp;
				else:
					$output["code"] = 204;
					$output["msg"] = "No Record Found";
				endif;

				echo json_encode($output,true);
			break;

			case 'get_talking_about':
                $result=$output="";
                if(isset($cfbToken) && !empty($cfbToken))
					$_SESSION['cfbToken'] = $cfbToken;

                $startdate = (isset($startdate) && !empty($startdate)) ? $startdate : START_DATE;
                $enddate = (isset($enddate) && !empty($enddate)) ? $enddate: END_DATE;

                $param_array=array(
                        "x0" => $company,
                        "startdate" => $startdate,
                        "enddate" => $enddate,
                        "platform" => "twitter.com",
                        "limit"   => "0,8",
                        "cfbToken" => $cfbToken
                        );
                $result=$confab->get_mentions($param_array);
                
                if(count($result) > 0 ){
                    $output["code"] = 200;
                    $output["status"] = "get influencers data Successfully";
                    $output["payload"]['data'] = $result;
                }else{
                    $output["code"] = 400;
                    $output["status"] = "No response";
                    $output["payload"]['data'] = "";
                }

                echo json_encode($output);
            break;

            case 'get_overview':

            		$startDate=substr(substr($startdate,4),0,12);
            		$end = substr(substr($enddate,4),0,12);
            		
            		$endDate=date('d-m-Y', strtotime('-1 day', strtotime($end)));					
            		$query="SELECT DATE(FROM_UNIXTIME(insert_time)) AS ForDate,COUNT(*) AS NumMentions FROM ".MENTIONS." WHERE insert_time >".strtotime($startDate)." AND insert_time<".strtotime($endDate)." AND status in(0,1) GROUP BY DATE(FROM_UNIXTIME(insert_time)) ORDER BY ForDate";
            		$totalPending =$mysqli->rawQuery($query);


            		$query1="SELECT DATE(FROM_UNIXTIME(tagged_time)) AS ForDate,COUNT(*) AS NumMentions FROM ".MENTIONS." WHERE tagged_time >".strtotime($startDate)." AND tagged_time<".strtotime($endDate)." AND status=7 GROUP BY DATE(FROM_UNIXTIME(tagged_time)) ORDER BY ForDate";
            		$totalTagged =$mysqli->rawQuery($query1);

            		
            		foreach ($totalTagged as $key => $Tvalue) {
            			$tagged_array[$Tvalue['ForDate']]=$Tvalue['NumMentions'];
            		}
            		// print_r($tagged_array);

            		foreach ($totalPending as $key => $Pvalue) {
            			$pending_array[$Pvalue['ForDate']]=$Pvalue['NumMentions'];
            		}
            		// print_r($pending_array);
            		// exit;

            		// echo $mysqli->getLastQuery();

            		$filter_param["startdate"] = $startDate;
            		$filter_param["enddate"] = $endDate;
            		
            		$tagged_mentions = $confab->get_tagged_mention_count($filter_param);
            						
            		$ignored_mentions = $confab->get_ignore_mention_count($filter_param);

            		$tagged_mentions_dimensions = $confab->get_tagged_mention_by_dimensions($filter_param);

            		$pending_mentions_dimensions = $confab->get_pending_mention_by_dimensions($filter_param);

            	
            		
            		$stats = array();
            		 

            		if(count($pending_mentions_dimensions) && !empty($pending_mentions_dimensions)):
            			foreach($pending_mentions_dimensions as $key => $value):
            				
            					

            				$stats[$value['dimension_attribute']]['dimension_id'] = $value['id'];
            				$stats[$value['dimension_attribute']]['pending_count'] = $value['cnt'];
            				$stats[$value['dimension_attribute']]['tagged_count'] = 0;
            			endforeach;
            		endif;


            		if(count($tagged_mentions_dimensions) && !empty($tagged_mentions_dimensions)):
            			foreach($tagged_mentions_dimensions as $key=>$value):
            				if(!isset($stats[$value['dimension_attribute']]['pending_count']))
            					$stats[$value['dimension_attribute']]['pending_count'] = 0;
            				
            				$stats[$value['dimension_attribute']]['dimension_id'] = $value['id'];
            				$stats[$value['dimension_attribute']]['tagged_count'] = $value['cnt'];
            				
            			endforeach;
            		endif;


            		$output["code"] = 200;
            		$output["payload"] = array(
            									"tagged_mentions" => $tagged_mentions,
            									"ignored_mentions" => $ignored_mentions,
            									"dimension_stats" => $stats,
            		

            									);



            		foreach ($pending_array as $key => $value) {
            		
            			$tagged_val = isset($tagged_array[$key]) ? $tagged_array[$key]:0;
            			$final[$key]=array('Pending'=>$pending_array[$key],'Tagged'=>$tagged_val);

            		}

            		
            		


            		
            		
            		$output['total']=$final;
            		echo json_encode($output);
            break;

            case 'cal_day_data':

            	$startDate=substr(substr($day,4),0,12);

            	$timestamp=strtotime($startDate);
            	$beginOfDay = strtotime("midnight", $timestamp);
				$endOfDay   = strtotime("tomorrow", $beginOfDay) - 1;

				$stime=date('m/d/Y', $beginOfDay);
				$etime=date('m/d/Y', $endOfDay);

				$filter_param=array(
									"startdate" =>$stime,
									"enddate" =>$etime
									);

				$tagged_count = $confab->get_tagged_mention_count($filter_param);
				$ignored_count = $confab->get_ignore_mention_count($filter_param);


				$mysqli->join(USER_CLIENTS_MAP." um","um.user_id=u.id","INNER");

				$mysqli->where('um.client_id',1);
				$mysqli->where('u.user_type',2);
				$result2=$mysqli->get(USERS." u",null,"u.user_email,u.user_name");

				foreach ($result2 as $key => $value) {
					$filter_param=array(
									"startdate" =>$stime,
									"enddate" =>$etime,
									"tagged_by" =>$value['user_email']
									);
					$count=$confab->get_tagged_mention_count($filter_param);
					
					if(isset($count) && !empty($count)){
						$tagged_done[]=array(
									"count"=>$count,
									"email" =>$value['user_name']
									);
					}
				}

				function sortByOrder($a, $b) {
				    return $b['count'] - $a['count'];
				}

				usort($tagged_done, 'sortByOrder');
				$output['code']=200;
				$output['payload']=$tagged_done;
				$output['total_tagged']=$tagged_count;
				$output['day']=$startDate;
				
				echo json_encode($output);
				
			break;


			case 'orm_users':
				
				// $dbhost='52.35.237.104'; 
				// define('DB_HOST',$dbhost);
				define('DB_USERNAME','hradmin');
				define('DB_PASSWD','tJMK8lqKLv3O');
				define('DB_NAME','livehrms');
				$hrms_db =new Mysqlidb ("52.35.237.104",DB_USERNAME,DB_PASSWD,'livehrms');

				
				$query="select iEmpId,vFirstName,vPinMailId,vPhotoUrl,
				IF(iDOB >0, date_format(from_unixtime(iDOB),'%m-%d'), '') as iDOB,
				IF(iDOJ < unix_timestamp(curdate()), date_format(from_unixtime(iDOJ),'%m-%d'), '') as iDOJ,
				IF(iAnniversary >0, date_format(from_unixtime(iAnniversary),'%m-%d'), '') as iAnniversary
				from hrms_emp_personal_details where eAccStatus='active' and iEmpId not in(446,464,461) and iDeptId=2 and rolls=13 order by vFirstName";
				
				$result=$hrms_db->rawQuery($query);

				// print_r($result);exit;

				foreach ($result as $key => $value) {
					$vPhotoUrl = ($value['vPhotoUrl']=='' ? 'blankprofile.gif' : $value['vPhotoUrl']);
					$empList['_'.$value['iEmpId']]=array($value['vFirstName'],$vPhotoUrl,$value['iDOB'],$value['iDOJ'],$value['vPinMailId']);
				}

				// print_r($empList);exit;

				$query1 = "select distinct ha.iEmpId as EmpID,from_unixtime(ha.iLogin) as LoginTime,
				ha.iIp1 as LoginIP, if(ha.iLogout!=0, from_unixtime(ha.iLogout),0)  as LogoutTime
				from hrms_attendance ha join hrms_emp_personal_details hepd
				on ha.iEmpId = hepd.iEmpId where date(from_unixtime(ha.iDate))=curdate()";

				$result1=$hrms_db->rawQuery($query1);

				foreach($result1 as $row) {
				
					  $empLoginStatus['_'.$row['EmpID']]=array('LoginTime'=>$row['LoginTime'],'LoginIP'=>$row['LoginIP'],'LogoutTime'=>$row['LogoutTime']);
					  
				}


				$query2 = "select vIP from hrms_IP where eStatus='active' ";
				$result2=$hrms_db->rawQuery($query2);
				
				foreach($result2 as $key=>$iprow) {
					 $ipList[]=$iprow['vIP'];
				}
				

				// print_r($empLoginStatus);exit;

				$finalJSONRecords = array();
				$time = time();
				
				foreach($empList as $key=>$val){
				
					$status='';
					
					$loginTime  = $empLoginStatus[$key]['LoginTime'];
					$logoutTime  = $empLoginStatus[$key]['LogoutTime'];
                    $logintimestamp = strtotime($empLoginStatus[$key]['LoginTime']);
                    $logouttimestamp = strtotime($empLoginStatus[$key]['LogoutTime']);
                    $workinHourtimestamp = ($empLoginStatus[$key]['LogoutTime']==0 ? ($time-$logintimestamp)-19800 : ($logouttimestamp-$logintimestamp)-19800);
                    $empLoginStatus[$key]['WorkingHour'] = date('H:i',$workinHourtimestamp);
					$loginIP  =   $empLoginStatus[$key]['LoginIP'];
					
					if($loginTime!='' && $logoutTime==0 && in_array($loginIP,$ipList) ){
						$status  ='login-office';	
					}
					else if($loginTime!='' && $logoutTime!='' && in_array($loginIP,$ipList)){
						$status  ='logout-office';	
					}
					else if($loginTime!='' && $logoutTime==0 && !in_array($loginIP,$ipList)){
						$status  ='login-home';	
					}
					else if($loginTime!='' && $logoutTime!='' && !in_array($loginIP,$ipList)){
						$status  ='logout-home';	
					}
					else if($loginTime=='' && $logoutTime=='' && array_key_exists($key, $empListHoliday)){
						$status  ='on-leave';	
					}
					else{
						$status  ='at-home';	
					}
					
					
					
					$finalJSONRecords[$key]  = $empLoginStatus[$key];
					
					$finalJSONRecords[$key]['Name']  = $val[0];
					$finalJSONRecords[$key]['email']  = $val[4];

					//******Check photo exists*********//
					if (file_exists("../upload/images/users/".$val[1])){
					
						$finalJSONRecords[$key]['Photo']  = $val[1];
					}
					else{
						$finalJSONRecords[$key]['Photo']  = 'blankprofile.gif';
					}
					//******Check photo exists*********//
					
											
					
					
					$finalJSONRecords[$key]['status']  = $status;

					$loginTime=split(" ", $loginTime);
					$loginTime=split(":", $loginTime[1]);
					$intime=$loginTime[0].":".$loginTime[1];

					$logoutTime=split(" ", $logoutTime);
					$logoutTime=split(":", $logoutTime[1]);
					$outtime=$logoutTime[0].":".$logoutTime[1];

					// $logoutTime=



					
					
					$className ='';
					$finalJSONRecords[$key]['className']  = $className;
					$finalJSONRecords[$key]['intime']  = $intime;
					$finalJSONRecords[$key]['outtime']  = $outtime;

				}

				// print_r($finalJSONRecords);exit;
				
				

				

				$output['payload']=$finalJSONRecords;
				echo json_encode($output);

				// print_r($result);exit;


			break;

			case 'user_stats':

				$timestamp=strtotime(date('d-m-Y'));
        		$beginOfDay = strtotime("midnight", $timestamp);
				$endOfDay   = strtotime("tomorrow", $beginOfDay) - 1;

				// echo "Start=".$beginOfDay."end=".$endOfDay;exit;

				//array of compnies assigned to user.
		 		$companies_assigned=unserialize(COMPANIES_ASSIGNED);

		 		// print_r($companies_assigned);exit;

		 		//Taking dimention id and attribute of  All tagged, QC , and ignore mentions to find unassinged Companies tagged count .
		 		$mysqli->join(MENTIONS_DETAILS." md","m.id=md.m_id","INNER");
		 		$mysqli->join('confab_app.dimensions as d',"md.x0=d.id");
		 		$mysqli->where('m.tagged_time',array($beginOfDay,$endOfDay),"BETWEEN");
		 		$mysqli->where('m.tagged_by',$mail_id);
		 		$mysqli->where('d.client_id',1);
				$mysqli->where('d.dimension_entity','x0');
				$mysqli->where('m.status',7);
				$mysqli->groupBy('d.id');
		 		$get_all_tagged=$mysqli->get(MENTIONS." m",null,"count(d.id) as cnt,d.id,d.dimension_attribute");

		 		$mysqli->join(MENTIONS_DETAILS." md","m.id=md.m_id","INNER");
		 		$mysqli->join('confab_app.dimensions as d',"md.x0=d.id");
		 		$mysqli->where('m.tagged_time',array($beginOfDay,$endOfDay),"BETWEEN");
		 		$mysqli->where('m.tagged_by',$mail_id);
		 		$mysqli->where('d.client_id',1);
				$mysqli->where('d.dimension_entity','x0');
				$mysqli->where('m.status',2);
				$mysqli->groupBy('d.id');
		 		$get_all_qc=$mysqli->get(MENTIONS." m",null,"count(d.id) as cnt,d.id,d.dimension_attribute");

		 		$mysqli->join(MENTIONS_DETAILS." md","m.id=md.m_id","INNER");
		 		$mysqli->join('confab_app.dimensions as d',"md.x0=d.id");
		 		$mysqli->where('m.tagged_time',array($beginOfDay,$endOfDay),"BETWEEN");
		 		$mysqli->where('m.tagged_by',$mail_id);
		 		$mysqli->where('d.client_id',1);
				$mysqli->where('d.dimension_entity','x0');
				$mysqli->where('m.status',3);
				$mysqli->groupBy('d.id');
		 		$get_all_ignore=$mysqli->get(MENTIONS." m",null,"count(d.id) as cnt,d.id,d.dimension_attribute");
		 		

		 		

		 		foreach ($companies_assigned as $user => $company) {

		 			
		 			if($mail_id==$user){
		 
		 				foreach ($company as $key=>$comp_id) {
		 					
		 					//Checking if dimention id is assigned or not.
		 					if($comp_id==$get_all[$key]['id']){
		 						
		 					}else{
		 						$unassigned_comp[$get_all_tagged[$key]['dimension_attribute']]=array(
		 							"tagged"=>$get_all_tagged[$key]['cnt'],
		 							"qc"=>isset($get_all_qc[$key]['cnt']) ? $get_all_qc[$key]['cnt'] : 0,
		 							"ignore"=>isset($get_all_ignore[$key]['cnt']) ? $get_all_ignore[$key]['cnt'] : 0
		 							);
		 					}

		 					for($i=$comp_id[0];$i<=$comp_id[1];$i++){

		 						
		 						$mysqli->join(MENTIONS_DETAILS." md","m.id=md.m_id","INNER");
		 						$mysqli->join('confab_app.dimensions as d','md.x0=d.id',"INNER");
		 						$mysqli->where('m.tagged_by',$user);
		 						$mysqli->where('md.x0',$i);
		 						$mysqli->where('m.tagged_time',array($beginOfDay,$endOfDay),"BETWEEN");
		 						$mysqli->where('d.client_id',1);
		 						$mysqli->where('d.dimension_entity','x0');
		 						$mysqli->where('m.status',7);
		 						// $mysqli->orderBy('count(*)','DESC');
		 						$count_ment[]=$mysqli->get(MENTIONS." m",null,'COUNT(*) as cnt,d.dimension_attribute,d.id');
		 						// echo $mysqli->getLastQuery();
		 						$mysqli->join(MENTIONS_DETAILS." md","m.id=md.m_id","INNER");
		 						$mysqli->join('confab_app.dimensions as d','md.x0=d.id',"INNER");
		 						$mysqli->where('m.tagged_by',$user);
		 						$mysqli->where('md.x0',$i);
		 						$mysqli->where('m.tagged_time',array($beginOfDay,$endOfDay),"BETWEEN");
		 						$mysqli->where('d.client_id',1);
		 						$mysqli->where('d.dimension_entity','x0');
		 						$mysqli->where('m.status',2);
		 						$count_ment_qc[]=$mysqli->get(MENTIONS." m",null,'COUNT(*) as cnt,d.dimension_attribute,d.id');
		 						$mysqli->join(MENTIONS_DETAILS." md","m.id=md.m_id","INNER");
		 						$mysqli->join('confab_app.dimensions as d','md.x0=d.id',"INNER");
		 						$mysqli->where('m.tagged_by',$user);
		 						$mysqli->where('md.x0',$i);
		 						$mysqli->where('m.tagged_time',array($beginOfDay,$endOfDay),"BETWEEN");
		 						$mysqli->where('d.client_id',1);
		 						$mysqli->where('d.dimension_entity','x0');
		 						$mysqli->where('m.status',3);
		 						$count_ment_ignore[]=$mysqli->get(MENTIONS." m",null,'COUNT(*) as cnt,d.dimension_attribute,d.id');
		 						
		 					}		
		 				}
		 				// print_r($unassigned_comp);exit;

		 				

		 			break;	
		 			}
		 			
		 			
		 		}

		 		//get the last tagged mention to find last seen of user.
		 		$mysqli->where('ml.action_by',$mail_id);
		 		$mysqli->orderBy('ml.id',"DESC");
		 		$last_tagged=$mysqli->getOne(MENTION_LOG." ml","ml.insert_time");

		 		// $date = date('d-m-Y', $last_tagged['insert_time']);
		 		$seen = date('G.i', $last_tagged['insert_time']);

		 		if(date('Ymd') == date('Ymd', $last_tagged['insert_time'])){
		 			$lastseen="Today ".$seen;
		 		}else{
		 			$lastseen=date('D',$last_tagged['insert_time'])." ".$seen;
		 		}


		 		foreach ($count_ment as $key => $value) {
		 			if(isset($value[0]['cnt']) && !empty($value[0]['cnt'])){
		 				$final_array[$value[0]['dimension_attribute']]=array(
		 					'tagged_count'=>$value[0]['cnt'],
		 					'qc_count'=>$count_ment_qc[$key][0]['cnt'],
		 					'ignore_count'=>$count_ment_ignore[$key][0]['cnt'],
		 					);
		 			}
		 		}
		 		// print_r($final_array);
		 		// print_r($unassigned_comp);
		 		$output['assigned']=$final_array;
		 		$output['unassigned']=$unassigned_comp;
		 		$output['email']=$mail_id;
		 		$output['lastseen']=$lastseen;
		 		$output['intime']=$intime;
		 		$output['outtime']=$outtime;
		 		echo json_encode($output);



			break;

			default:
      			$ajax_token = false;

		endswitch;

	endif;
