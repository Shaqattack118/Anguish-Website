/**
 * Anguish Vote Page
 */
var AnguishVotePage = new function AnguishVotePage() 
{
	
	/** item limit **/
	this.itemLimit = 5;
	
	/** Current Page **/
	this.currentPaginationPage = 1;
	
	/** pagination Items**/
	this.paginationItems = { };
	
	/** Items currently selected **/
	this.selectedItems = {};
	
	/** Cached category JSON **/
	this.cachedJSON = {};
	
	/** Product cost cache **/
	this.productCost = { };
	
	/** product info cache **/
	this.productInfo = { };
	
	/** current points **/
	this.currentPoints = 0;
	
	/** Are we logged in? **/
	this.isLoggedIn = false;
	
	/** Session Id to be passed **/
	this.sessionId = -1;
	
	var instance = this;
		
	AnguishDonationPage.getInstance = function()
	{
		return instance;
	}
	
	/**
	 * Init handling
	 */
	this.init = function(isLoggedIn, sessionId,  points){
		
		this.currentPoints = points;
		this.isLoggedIn = isLoggedIn;
		this.sessionId = sessionId;
		
		this._createTabs();
		
		var $this = this;
		
		/** Load all Products for preprocessing **/
		this._getAllProducts();
		

		/** Load Dom events **/
		this.loadEvents();
		
	}
	
	/**
	 * Update buy text
	 */
	this._updateBuyText = function(count){
		if(count != 0){
			$(".cartBtn").html("("+count+") View Cart");
		} else
			$(".cartBtn").html("View Cart");
	}
	
	/**
	 * load events 
	 */
	this.loadEvents = function(){
		var $this = this;
		
		$("#purchase").click(function(e) {
			
			vex.dialog.open({
				  message: 'Please enter the username whom will be recieving these items!',
				  input: "<input name=\"username\" type=\"text\" placeholder=\"Username\" required />",
				  buttons: [
				    $.extend({}, vex.dialog.buttons.YES, {
				      text: 'Purchase'
				    }), $.extend({}, vex.dialog.buttons.NO, {
				      text: 'Cancel'
				    })
				  ],
				  callback: function(data) {
				  	
				  	/** Better to ask for confirmation **/
				    if (data){
				    	
					  	vex.dialog.confirm({
							  message: 'Are you absolutely sure you want to recieve these items on <br><b>' + data.username + '</b>',
							  callback: function(value) {
							  	if(value)
							   	$this.purchaseItems(data.username, $this.purchaseCallback);
							  }
							});
							
				  	}
				  }
				});
				
			 });
			 	 
		$(".redemptionHistory").click(function(e) { $this._getRedemptionHistory();});
		$(".redemptionCenter").click(function(e) {  $this._getDonationJSON('0', function(obj){ $this.cachedJSON['0'] = obj; $this._renderPage(obj); }); });
	
		$(".purchasePoints").click(function(e) { $this._buyPointsEvent();});
		$(".purchasePointsHistory").click(function(e) { $this._getPaymentHistory();});
		$(".redeemPin").click(function(e) { $this._redeemPinEvent();});
		$(".checkPin").click(function(e) { $this._checkPinEvent();});
		
	
	
		$("#modal-three").find("#closeBtn").click(function(e) { $("#modal-three").removeClass("show").addClass("hideSection"); });
		
		$(".redemptionCenter").trigger('click');
	}
	
	/**
	 * Redeem Pin
	 */
	this._redeemPinEvent = function(){
		

		var $this = this;
			if(!$this.isLoggedIn){
				showNotification("Please Login","You must be registered on our forums to redeem pins!");	
				return;
			}
				vex.dialog.open({
				  message: 'Enter your username and pin',
				  input: "<input name=\"username\" type=\"text\" placeholder=\"Username\" required />\n<input name=\"pin\" type=\"text\" placeholder=\"Pin\" required />",
				  buttons: [
				    $.extend({}, vex.dialog.buttons.YES, {
				      text: 'Redeem'
				    }), $.extend({}, vex.dialog.buttons.NO, {
				      text: 'Cancel'
				    })
				  ],
				  callback: function(data) {
				    if (data){
				    		$this._redeemPin(data.username, data.pin);
				  	}
				  }
				});
		}
	
	
	/*
	 * Check Pin Event
	 */
	this._checkPinEvent = function(){
		
		vex.dialog.open({
				  message: 'Please enter the pin you wish to check.',
				  input: "<input name=\"pin\" type=\"text\" placeholder=\"Pin\" required />",
				  buttons: [
				    $.extend({}, vex.dialog.buttons.YES, {
				      text: 'Check'
				    }), $.extend({}, vex.dialog.buttons.NO, {
				      text: 'Cancel'
				    })
				  ],
				  callback: function(data) {
				  
				    if (data){
				    	
				    		var pin = data.pin;
										
								/** Callback for pop up **/	
					    	var pinCallBack = function(json) {
						
										var obj = JSON.parse(json);	
								
										var code = obj.Code;
										var message = obj.Message;
							
										var sucessMessage = "The Pin: {pin} <b><font color='green'>{message}</font></b>";
										var badMessage = "The Pin: {pin} <b><font color='red'>{message}</font></b>";
										
										badMessage = badMessage.replace('{pin}', pin);
										badMessage = badMessage.replace('{message}', message);
										
										sucessMessage = sucessMessage.replace('{pin}', pin);
										sucessMessage = sucessMessage.replace('{message}', message);
										
										switch(code){
											case 200:
												showNotification("Success", sucessMessage );
												break;
											case 480:
											case 485:
												showNotification("Error!", badMessage);
												break;
											default:
												showNotification("Unknown Error!", badMessage);
										
										};
									};
										
								var params =  {
															'action' : 'checkPin',
															'pin' : pin 
														};
						
							$.get(API_ENDPOINT, params, function(e) { pinCallBack(e); });
				  	
				  	}
			 		}
			 });	

	}
	/**
	 * AJAX post to redeem our pin
	 */
	this._redeemPin = function(username, pin){
		
	
		var pinCallBack = function(json) {
			
							var obj = JSON.parse(json);	
					
							var code = obj.Code;
							var message = obj.Message;
							
							var sucessMessage = "Your ingame account has successfully been credited with the pin: {pin} <br> Happy Gaming!";
							var badMessage = "The Pin: {pin} <b><font color='red'>{message}</font></b>";

							sucessMessage = sucessMessage.replace('{pin}', pin);
							
																	
							badMessage = badMessage.replace('{pin}', pin);
							badMessage = badMessage.replace('{message}', message);
										
							switch(code){
								case 200:
									showNotification("Success", sucessMessage );
									break;
								
								case 480:
								case 485:
									showNotification("Error!", badMessage);
									break;
								default:
									showNotification("Unknown Error!", badMessage);
							
						};
				};
				
		var params =  {
										'action' : 'redeemPin',
										'username' : username,
										'pin' : pin 
									};

		$.post(API_ENDPOINT, params, function(e) { pinCallBack(e); });
	}
	
	
	/**
	 * purchase callback
	 */
	this.purchaseCallback = function($this, json){
		
		var data = JSON.parse(json);	
		
		var code = data.Code;
		var message = data.Message;
		
		switch(code){
			case 200:
				$this.selectedItems = { };
				$this._updateBuyText(0);
				$this._reSelectRadios();
				$this._updateShoppingCart();
				showNotification("Success", "Your ingame account has successfully been credited. Please ::check ingame with a empty inventory. <br> Happy Gaming!");
				break;
			
			case 440:
			case 450:
				showNotification("Error!", message);
				break;
			default:
				showNotification("Unknown Error!", code + " "+ message);
			
		}
	}
	
	/**
	 * Purchase Item
	 */
	this.purchaseItems = function(person, callback){

		var $this = this;
		var data = $this.selectedItems;
		var cart = [];
		
		for (var key in data){

	  	if (data.hasOwnProperty(key)) 
	  		cart.push(key);
		  
		}
		
		var params =  {
										'action' : 'purchase',
										'username' : person,
										'sessionId' : $this.sessionId,
										'cart' : cart 
									};

		$.post(API_ENDPOINT, params, function(e) { callback($this, e); });
		
	}

	/**
	 * Update current available points
	 */
	this.updateCurrentAvailablePoints = function(cost, type){
		
		if(type == 'add')
			this.currentPoints = this.addToTotalCost(cost);
		else
			this.currentPoints = this.removeFromTotal(cost);
		
		$(".apoint").empty().html(this.currentPoints);
	}
	/*
	 * Select Radios already selected 
	 */
	this.selectRadio = function(e){
		var targ = $(e.currentTarget);
		var $this = this;
		var productId = targ.attr("productId");
		var productCost = parseInt($this.productCost[productId]);
		
		var type = 'add';
		/** We are unselecting **/
		if($this.selectedItems[productId]){
			type = 'remove';
			delete $this.selectedItems[productId];
			targ.prop("checked", false);
			$this._updateShoppingCart();
		} else {
			/** We can buy this product **/
			if($this.canBuyThisItem(productCost)){
				$this.selectedItems[productId] = true;
				$this._updateShoppingCart();
				targ.prop("checked", true);
			} else {
				if($this.isLoggedIn)
					showNotification("Error","You need "+ -(this.currentPoints - productCost)  +" more donator points to purchase this item!");
				else
					showNotification("Please Login","You need login on the forums to purchase items!");
					
				targ.prop("checked", false);
				return;
			}			
		}
		
		$this.updateCurrentAvailablePoints(productCost, type);
		
		$this._updateBuyText(Object.size($this.selectedItems));
}
	
	

	/** Reselect radio buttons upon rending **/
	this._reSelectRadios = function(){
		
		$('.donationTable').css({'left' : ''});
		$('.donationTable').find(".radio").prop("checked", false);
		
		var data = this.selectedItems;
		for (var key in data) {
	  	if (data.hasOwnProperty(key)) {
	  		
	    	var item = $('.donationTable').find(".radio[productId='"+key+"']");
	   	
	   		if(item)
	   			item.prop("checked", true);
		  }
		}
	}
	/**
	 * Create Table Row based on json structure 
	 */
	this._createRows = function (tableData){
	
		var table = $('.contentArea');
		
		table.removeClass().addClass("contentArea donationTable");
		table.empty();
		
		var $this = this;
		
		table.find(".row").remove(); // remove all .rows since we are redrawing
		
		_.each(tableData, function(obj){
		
			var row = create("tr").addClass("row");
				
			var productId = obj.productId;
			var productCost = obj.cost;
			
		  var radioButton = create("input").addClass("radio").attr("type", "radio").attr("productId", productId).click(function(e) { $this.selectRadio(e);  });

			row.append(create("td").addClass("buy").append(radioButton));
			row.append(create("td").addClass("name").append(create("img").attr("src", obj.picture), " ", obj.name));
			row.append(create("td").addClass("cost").append(productCost));

			table.append(row);
			
		});
		
		
	};
	
	/** Render empty notice **/
	this._renderEmpty = function() {
		
		var box = create("div").addClass("box notLoggedIn notice");
		box.append(create("h3").append("Notice!"))
		box.append(create("p").append("Only logged in users may view this section."));
	
		return box;
	}
	

						
	/** Render purchase table row **/
	this._renderPaymentHistoryPurchaseTableRow = function(table, obj){
		
				var $this = this;
			
				var row = create("tr").addClass("itemHistoryRow").appendTo(table);
						
				create("td").addClass("navigation-item").append("Order Id").appendTo(row);
				create("td").addClass("navigation-item").append("Product").appendTo(row);
				create("td").addClass("navigation-item").append("Price Paid").appendTo(row);

					
				var row = create("tr").addClass("itemHistoryRow").appendTo(table);
				var product = INDEXED_BMT_PRODUCTS[obj.productId][0];

				create("td").append(obj.ordernumber).appendTo(row);
				create("td").append(create("img").attr("src", product.picture), " ", product.name).appendTo(row);
				create("td").append('<b>'+obj.total+'</b>').appendTo(row);

		
				var row = create("tr").addClass("itemHistoryRow").appendTo(table);
				create("td").attr("colspan", 2).append("Total Paid: ", obj.total).appendTo(row);

	}				
					
	/** Render purchase table row **/
	this._renderPurchaseTableRow = function(table, arr){
		
				var $this = this;
			
				var row = create("tr").addClass("itemHistoryRow").appendTo(table);
						
				create("td").addClass("navigation-item").append("Transaction Id").appendTo(row);
				create("td").addClass("navigation-item").append("Product").appendTo(row);
				create("td").addClass("navigation-item").append("Account Redeemd").appendTo(row);
				create("td").addClass("navigation-item").append("Price Paid").appendTo(row);

				var totalCost = 0;
				
		_.each(arr, function(obj){
				
					
				var row = create("tr").addClass("itemHistoryRow").appendTo(table);
				var transId = obj.transactionId;
				var productId = obj.productId;
				var userName = obj.username;
				var price = obj.price;


				var prod = $this.productInfo[productId];
				
				totalCost += parseInt(price);

						
				create("td").append(transId).appendTo(row);
				create("td").append(create("img").attr("src", prod.picture), " ", prod.name).appendTo(row);
				create("td").append('<b>'+userName+'</b>').appendTo(row);
				create("td").append('<b>'+price+'</b>').appendTo(row);

		});
		
				var row = create("tr").addClass("itemHistoryRow").appendTo(table);
				create("td").attr("colspan", 3).append("Total Points Used: ", totalCost).appendTo(row);

	}

	/**
	 *  PreProcess our RedemtionHistory _preprocessRedemptionHistory
	 */
	this._preprocessHistory  = function(arr, callback){

		var $this = this;
				
		// preprocess data
		
		var currentCount = 0;
		var currentPage = 1;
		
		/** Reset **/
		this.paginationItems = {};
		
		console.log(arr);
		/** Loop through indexed data **/
		for (var key in arr) {
			
				if (arr.hasOwnProperty(key)) {
					
						var obj = arr[key];
						
						/** Did we exceed our item limit? **/
						if(currentCount < $this.itemLimit){
							
							if(!this.paginationItems[currentPage])
									this.paginationItems[currentPage] = [];
								
						
							this.paginationItems[currentPage].push(obj);
							currentCount++;
	
						} else {
						currentCount = 0;
						currentPage++;
					}
				}
		}
			
		callback();

 
	}
	
	
	this._renderPaginateHistory = function(pageNum, title, drawMethod){
		
						
		$(".box.donation").find(".title").html(title);
		
	
		this.currentPaginationPage = pageNum;
			
		var table = $("table.contentArea").empty();
			
		table.removeClass().addClass("contentArea historyTable");
		$(".tab-links").hide();		
		

		var $this = this;
			
			
		var arr = $this.paginationItems[pageNum];
		
		_.each(arr, function(obj){

		  	var tableHeaders = create("tr").addClass("header navigation-item").appendTo(table);
				tableHeaders.click(function(){
					$(this).nextUntil('tr.header').css('display', function(i,v){ 
						if (this.style.display === 'table-row') 
							return 'none';
						else 
							return 'table-row';
						});
					table.css({'left' : 0});
					});
				
				drawMethod(table, tableHeaders, obj);

		});
			
	}
	
	
	
	/**
	 *  Render a Paginated items by PageNumber
	 */
	this._renderRedeemHistory = function($this, pageNum){

		var callback = function(table, tableHeaders, obj){
							
					
					create("td").attr("colspan", 2).append(obj[0].transactionId).appendTo(tableHeaders);
					create("td").append(obj[0].boughtdate).appendTo(tableHeaders);
							
							
					$this._renderPurchaseTableRow(table, obj);		
		
		};
		
		$this._renderPaginateHistory(pageNum, 'Shopping History', callback);
	
	  /** Was not found, so append **/
		if($(".box.donation").find("#compact-pagination").length == 0)
				$(".box.donation").append('<div id="compact-pagination" class="compact-theme simple-pagination"><ul class ="pagination"></ul></div>');	
					
		
		$this._renderPaginationItem($this._renderRedeemHistory);
	}
	
	/**
	 *  Render a Paginated items by PageNumber
	 */
	this._renderPaymentHistory = function($this, pageNum){
		
		var callback = function(table, tableHeaders, obj){
							
					create("td").attr("colspan", 2).append(obj[0].ordernumber).appendTo(tableHeaders);
					create("td").append(obj[0].orderdate).appendTo(tableHeaders);
							
							
					$this._renderPaymentHistoryPurchaseTableRow(table, obj[0]);		
		
		};
		
		$this._renderPaginateHistory(pageNum, 'Payment History', callback);
	
	  /** Was not found, so append **/
		if($(".box.donation").find("#compact-pagination").length == 0)
				$(".box.donation").append('<div id="compact-pagination" class="compact-theme simple-pagination"><ul class ="pagination"></ul></div>');	
					
		$this._renderPaginationItem($this._renderPaymentHistory);
	}
	
	/**
	 * Render Pageination Item
	 */
	this._renderPaginationItem = function(clickCallBack){
		
		var ul = $(".box.donation").find(".pagination");
		
		ul.empty();
		
		ul.append(create("li").addClass("active").append(create("span").addClass("current prev").append("<")));
		
		var $this = this;
		var arr = $this.paginationItems;
		
		
		/** Loop through our indexed item by Page number **/
			for (var pageNum in arr) {
			
				if (arr.hasOwnProperty(pageNum)) {
					
					var li = create("li")
				
						/** We are on the page, so this must be active? **/
						if(pageNum == $this.currentPaginationPage){
								li.append(create("span").addClass("current").append(pageNum));
								li.addClass("active");
						} else {
								li.append(create("a").addClass("page-link").append(pageNum)).attr("pageNum", pageNum).click(function(e) { clickCallBack($this, $(this).attr("pageNum")); }) ;							
						}
							ul.append(li);
					}
			}
		ul.append(create("li").addClass("active").append(create("span").addClass("current next").append(">")));

	}
	
	/**
	 * Render a shopping item
	 */
	this._renderShoppingItem = function(obj){
		
		var list = create("li");
		
		var ammount = "1x ";
		var image = create("img").attr("src", obj.picture);
		var itemName = create("a").attr("href", "").append(obj.name);
		var itemCost = create("strong").append(obj.cost);
		
		
		list.append(create("span").append(ammount, image, itemName), itemCost);
		
		return list;
	}
	
	/**
	 * Render Points Item
	 */
	this._renderPointAreaItem = function(obj){
		
		var list = create("li");
		
		var ammount = "1x ";
		var image = create("img").attr("src", obj.picture);
		var itemName = create("a").addClass("purchasePointName").attr("href", "").append(obj.name);
		var itemCost = create("a").attr("href",obj.url+"&CCOM0="+this.sessionId).addClass("button purchasePointBtn").append("Purchase");
			
		 list.append(create("span").append(ammount, image, itemName), itemCost);
		
		return list;
	}
	
	/**
	 * Buy Points Event
	 */
	this._buyPointsEvent = function(){
		this._renderPointsArea();
		$("#modal-three").removeClass("hideSection").addClass("show");
	}; 

  /*
   * Render the Points Buying modal
   */
	this._renderPointsArea = function(){
		var shoppingList = $("#modal-three").find(".shoppingList");
		var $this = this;
		shoppingList.empty();
				
		$("#modal-three").find(".modal-footer").find("#purchase").hide();

		_.each(BMT_PRODUCTS, function(obj){
			shoppingList.append($this._renderPointAreaItem(obj));
		
		});
		
		
	}
	/**
	 * Update Shopping cart HTML
	 **/
	this._updateShoppingCart = function(){
		var shoppingList = $(".shoppingList");
		
			shoppingList.empty();
		
		
			$(".modal-footer").find("#purchase").show();

			var data = this.selectedItems;
			var totalCost = 0;
			for (var key in data) {
				if (data.hasOwnProperty(key)) {
					var obj = this.productInfo[key];
					totalCost += parseInt(obj.cost);
					shoppingList.append(this._renderShoppingItem(obj));
				}
			}
			
			// we have no cost
			if(totalCost == 0){
				shoppingList.append("Your Shopping Cart is empty :(");
				$(".modal-footer").find("#purchase").hide();
			}
			
			$(".totalAmt").html(parseInt(totalCost));
	}
	
	/**
	 * Tab click event
	 */
	this._tabClickEvent = function(e){
	
		var target = $(e.currentTarget);
		var type = target.attr("type");
		var $this = this;		
		/** Remove active as we are switching tabs **/
		$(".tab-links").find(".active").removeClass("active");
		
		target.addClass("active");

		this._getDonationJSON(type, function(obj) { $this.cachedJSON[type] = obj; $this._renderPage(obj) });

	};
	
	/**
	 * Create tabs, 
	 * Select the Tab links html that is in the page and append some stuff to it,
	 * note the TYPE, that is the category index for the items to be pulled from the API
	 */
	this._createTabs = function(){
	
		var ul = $(".tab-links");
		var $this = this;
		
		var misc = create("li").append(create("a").attr("type", 0).addClass("navigation-item active").append("Popular").click( function(e) { $this._tabClickEvent(e);  }));
		var armour = create("li").append(create("a").attr("type", 1).addClass("navigation-item").append("Weapons").click( function(e) { $this._tabClickEvent(e);  }));
		var weapons = create("li").append(create("a").attr("type", 2).addClass("navigation-item").append("Armour").click(function(e) { $this._tabClickEvent(e);  }));
		var rares = create("li").append(create("a").attr("type", 3).addClass("navigation-item").append("Rares").click( function(e) { $this._tabClickEvent(e);  }));

		
		ul.append(misc, armour, weapons, rares);
		
	}
	
	/**
	 * Example of how to get donation data, will be using AJAX shortly
	 */
	this._getDonationJSON = function(type, callback){

		var url = API_ENDPOINT+"?action=getItems&category="+type
		
		if(!this.cachedJSON[type]){
				$.get(url, callback);
		} else { // call the cached json 
			callback(this.cachedJSON[type]);
		}
	};
	
	
	/**
	 * query the API for all of our donation products, then we do some preprocessing
	 */
	this._getAllProducts = function(){

		var url = API_ENDPOINT+"?action=getAllItems";
		var $this = this;
		
		var callback = function(data){
			
			var json = JSON.parse(data);
			
				_.each(json, function(obj){
	
					var productId = obj.productId;
					var productCost = obj.cost;
					
					$this.productInfo[productId] = obj;
					
					$this.productCost[productId] = productCost;
					
				});
		}
	
			$.get(url, callback);
	
	};
	
	
	
	/**
	* Request to get Redemption History
	*/
	
	this._getRedemptionHistory = function(){

			var url = API_ENDPOINT+"?action=getRedemptionHistory&sessionId="+this.sessionId;

			var $this = this;
			
			var callback = function(r){
				
				var data = JSON.parse(r);
				
					if(data.length == 0){
						$('.donation').children().hide();
						$('.donation').append($this._renderEmpty());
						return;
					} else {
						$('.donation').children().show();
						$('.notLoggedIn').remove(); // remove our notice	
					}
					
				
					
				var trans = _.sortBy(data, "boughtdate").reverse(); // sort by boughtdate
				    trans = _.indexByArray(trans, 'transactionId'); // index it by transaction
		
					$this._preprocessHistory(trans, function(e) {  	$this._renderRedeemHistory($this, 1); } );
				
			};
			
			$.get(url, callback);
	
	}
	
	
	/*
	 * Get Payment History
	 */
	this._getPaymentHistory = function(){

			var url = API_ENDPOINT+"?action=getPaymentHistory&sessionId="+this.sessionId;

			var $this = this;
			
			var callback = function(r){
				
				var data = JSON.parse(r);
				
					if(data.length == 0){
						$('.donation').children().hide();
						$('.donation').append($this._renderEmpty());
						return;
					} else {
						$('.donation').children().show();
						$('.notLoggedIn').remove(); // remove our notice	
					}

				var trans = _.sortBy(data, "orderdate").reverse(); // sort by boughtdate
				    trans = _.indexByArray(trans, 'ordernumber'); // index it by transaction
		
				$this._preprocessHistory(trans, function(e) {  	$this._renderPaymentHistory($this, 1); } );
				
			};
			
			$.get(url, callback);
	
	}
	

	/**
	* Render the page
	*/
	this._renderPage = function(jsonIn){
		
		$('#compact-pagination').remove();
		$(".box.donation").find(".title").html("Donation Prizes");
		$('.box.donation').children().show();
		$('.notLoggedIn').remove(); // remove our notice																					 
		
		
		var json = JSON.parse(jsonIn);
		this._createRows(json);
		
		this._reSelectRadios();
	}
	
	this.addToTotalCost = function(productCost){
		return parseInt(this.currentPoints - productCost);
	}
	
	this.removeFromTotal = function(productCost){
		return parseInt(this.currentPoints + productCost);
	}
	
	this.canBuyThisItem = function(productCost){
		return (productCost <= this.currentPoints);
	}
	
	
	return AnguishVotePage;
}
