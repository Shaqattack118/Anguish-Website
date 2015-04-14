/**
 * Anguish Donation Page
 * Don't you wish you had a pro helping you?
 */
var AnguishDonationPage = new function AnguishDonationPage() 
{
	
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
		
		this._getAllProducts();
		

		
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
			
				var person = prompt("Please enter the username whom will be recieving these items!", "Who gets these?");
				
				if (person != null) 
				 $this.purchaseItems(person, $this.purchaseCallback);
				
				}
				
			 );
			 	 
		$(".redemptionHistory").click(function(e) { $this._getRedemptionHistory();});
		
		$(".redemptionCenter").click(function(e) { 
																							 $this._getDonationJSON('0', function(obj){ $this.cachedJSON['0'] = obj;
																																													 $this._renderPage(obj); });
																							 });
	
		$(".redemptionCenter").trigger('click');
		
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
				showNotification("Success", "Your ingame account has successfully been credited. Please ::check ingame with a empty inventory. Happy Gaming!");
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
	
	
	this.addToTotalCost = function(productCost){
		return parseInt(this.currentPoints - productCost);
	}
	
	this.removeFromTotal = function(productCost){
		return parseInt(this.currentPoints + productCost);
	}
	
	this.canBuyThisItem = function(productCost){
		return (productCost <= this.currentPoints);
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
				create("td").append(userName).appendTo(row);
				create("td").append(price).appendTo(row);

		});
		
				var row = create("tr").addClass("itemHistoryRow").appendTo(table);
				create("td").attr("colspan", 3).append("Total Points Used: ", totalCost).appendTo(row);

	}

	/**
	 *  Will render a table that is collapseable in a modal
	 */
	this._renderRedemptionHistory  = function(arr){
		
	
		
		var table = $("table.contentArea");
		
		table.removeClass().addClass("contentArea historyTable");
		
		$(".box.donation").find(".title").html("Redemption History");
		
		$(".tab-links").hide();		
		table.empty();

		
		var $this = this;
				
		for (var key in arr) {
				if (arr.hasOwnProperty(key)) {
						var obj = arr[key];

			  		var transId = key;
			  		var tableHeaders = create("tr").addClass("header navigation-item").appendTo(table);
					
						tableHeaders.click(function(){
						    $(this).nextUntil('tr.header').css('display', function(i,v){
						        if(this.style.display === 'table-row')
						        		return 'none' 
						        else
						        		return 'table-row';
						    });
						 		 table.css({'left' : 0});
						
							});
						
						create("td").attr("colspan", 3).append(transId).appendTo(tableHeaders);
						create("td").append(obj[0].boughtdate).appendTo(tableHeaders);
						
						
						$this._renderPurchaseTableRow(table, obj);
			}
		}

      
      
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
		
		var misc = create("li").append(create("a").attr("type", 0).addClass("navigation-item active").append("Misc").click( function(e) { $this._tabClickEvent(e);  }));
		var armour = create("li").append(create("a").attr("type", 1).addClass("navigation-item").append("Armour").click( function(e) { $this._tabClickEvent(e);  }));
		var weapons = create("li").append(create("a").attr("type", 2).addClass("navigation-item").append("Weapons").click(function(e) { $this._tabClickEvent(e);  }));
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
					
				
					
				var trans = _.sortBy(data, "boughtdate"); // sort by boughtdate
				    trans = _.indexByArray(trans, 'transactionId'); // index it by transaction
		
					$this._renderRedemptionHistory(trans);
				
			};
			
			$.get(url, callback);
	
	};
	
	
	
	/**
	* Render the page
	*/
	this._renderPage = function(jsonIn){
		
		
		$(".box.donation").find(".title").html("Donation Prizes");
		$('.box.donation').children().show();
		$('.notLoggedIn').remove(); // remove our notice																					 
		
		
		var json = JSON.parse(jsonIn);
		this._createRows(json);
		
		this._reSelectRadios();
	}
	
	return AnguishDonationPage;
}
