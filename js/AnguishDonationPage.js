/**
 * Donation Page Javascript,
 * TODO: Shopping cart and such validation 
 */
var AnguishDonationPage = new function AnguishDonationPage() 
{
	
	this.selectedItems = {};
	
	this.cachedJSON = {};
	
	this.productCost = { };
	
	this.productInfo = { };
	
	this.currentPoints = 0;
	
	this.isLoggedIn = false;
	this.memberId = -1;
	var instance = this;
		
	AnguishDonationPage.getInstance = function()
	{
		return instance;
	}
	
	/**
	 * Init handling
	 */
	this.init = function(isLoggedIn, memberId,  points){
		this.currentPoints = points;
		this.isLoggedIn = isLoggedIn;
		this.memberId = memberId;
		this._createTabs();
		var $this = this;
		this._getDonationJSON('0', function(obj) {  $this.cachedJSON['0'] = obj; $this._renderPage(obj) });
		
		this.loadEvents();
	}
	
	this._updateBuyText = function(count){
		if(count != 0){
			$(".cartBtn").html("("+count+") View Cart");
		} else
			$(".cartBtn").html("View Cart");
	}
	this.loadEvents = function(){
		var $this = this;
		$("#purchase").click(function(e) {
				var person = prompt("Please enter the username whom will be recieving these items!", "Who gets these?");
				if (person != null) 
				 $this.purchaseItems(person, $this.purchaseCallback) 
			 
				}
			 );
	}
	
	this.purchaseCallback = function(data){
		console.log(data);
	}
	
	this.purchaseItems = function(person, callback){
		
		//TODO: SEND REQUEST TO API.PHP THAT CONTAINS AN ARRAY OF PRODUCTIDS

		var data = this.selectedItems;
		
		var cart = [];
		
		for (var key in data) {
	  	if (data.hasOwnProperty(key)) {
	  		cart.push(key);
		  }
		}
		
		var params =  {
								'action' : 'purchase',
								'username' : person,
								'memberId' : this.memberId,
								'cart' : cart 
							};
							
		var url = "/website/api.php";
		
		console.log(params);
		$.post(url, params, callback);
		
	}

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
		} else {
			/** We can buy this product **/
			if($this.canBuyThisItem(productCost)){
				$this.selectedItems[productId] = true;
				$this._updateShoppingCart();
				targ.prop("checked", true);
			} else {
				if($this.isLoggedIn)
					alert("You need "+ -(this.currentPoints - productCost)  +" more donator points to purchase this item!");
				else
					alert("You need login on the forums to purchase items!");
					
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
	
		var table = $('.donationTable');
		
		var $this = this;
		
		table.find(".row").remove(); // remove all .rows since we are redrawing
		
		_.each(tableData, function(obj){
		
		var row = create("tr").addClass("row");
		
			var productId = obj.productId;
			var productCost = obj.cost;
			
			$this.productInfo[productId] = obj;
			
			$this.productCost[productId] = productCost;
			
		  var radioButton = create("input").addClass("radio").attr("type", "radio").attr("productId", productId).click(function(e) { $this.selectRadio(e);  });
		  
		  //$("#radio_1").prop("checked", true)
			row.append(create("td").addClass("buy").append(radioButton));
			row.append(create("td").addClass("image").append(create("img").attr("src", obj.picture)));
			row.append(create("td").addClass("name").append(obj.name));
			row.append(create("td").addClass("cost").append(productCost));

			table.append(row);
		});
		
		
	};
	
	this._renderShoppingItem = function(obj){
		
		var list = create("li");
		
		var ammount = "1x ";
		var image = create("img").attr("src", obj.picture);
		var itemName = create("a").attr("href", "").append(obj.name);
		var itemCost = create("strong").append(obj.cost);
		
		
		list.append(create("span").append(ammount, image, itemName), itemCost);
		
		return list;
	}
	
	this._updateShoppingCart = function(){
		var shoppingList = $(".shoppingList");
		
			shoppingList.empty();
		


			var data = this.selectedItems;
			var totalCost = 0;
			for (var key in data) {
				if (data.hasOwnProperty(key)) {
					var obj = this.productInfo[key];
					totalCost += parseInt(obj.cost);
					shoppingList.append(this._renderShoppingItem(obj));
				}
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

		var url = "/website/api.php?action=getItems&category="+type
		
		if(!this.cachedJSON[type]){
				$.get(url, callback);
		} else { // call the cached json 
			callback(this.cachedJSON[type]);
		}
	};
	
	/**
	* Render the page
	*/
	this._renderPage = function(jsonIn){
		
		var json = JSON.parse(jsonIn);
		this._createRows(json);
		
		this._reSelectRadios();
	}
	
	return AnguishDonationPage;
}
