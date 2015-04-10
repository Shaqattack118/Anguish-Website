/**
 * Donation Page Javascript,
 * TODO: Shopping cart and such validation 
 */
var AnguishDonationPage = new function AnguishDonationPage() 
{
	
	this.selectedItems = {};
	
	var instance = this;
		
	AnguishDonationPage.getInstance = function()
	{
		return instance;
	}
	
	/**
	 * Init handling
	 */
	this.init = function(){
		this._createTabs();
		var $this = this;
		this._getDonationJSON('0', function(obj) {  $this._renderPage(obj) });
	}
	
	/*
	 * Select Radios already selected 
	 */
	this.selectRadio = function(e){
		var targ = $(e.currentTarget);
		var $this = this;
		var itemId = targ.attr("itemId");
		
		/** We are unselecting **/
		if($this.selectedItems[itemId]){
			delete $this.selectedItems[itemId];
			targ.prop("checked", false);
		} else {
			$this.selectedItems[itemId] = true;
			targ.prop("checked", true);
		}
	}
	
	/** Reselect radio buttons upon rending **/
	this._reSelectRadios = function(){
		
		var data = this.selectedItems;
		
		for (var key in data) {
	  	if (data.hasOwnProperty(key)) {
	  		
	    	var item = $('.donationTable').find(".radio[itemId='"+key+"']");
	   	
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
		
			var itemId = obj.itemId;
			
		  var radioButton = create("input").addClass("radio").attr("type", "radio").attr("itemId", itemId).click(function(e) { $this.selectRadio(e);  });
		  
		  //$("#radio_1").prop("checked", true)
			row.append(create("td").addClass("buy").append(radioButton));
			row.append(create("td").addClass("image").append(create("img").attr("src", obj.picture)));
			row.append(create("td").addClass("name").append(obj.name));
			row.append(create("td").addClass("cost").append(obj.cost));

			table.append(row);
		});
		
		
	};
	
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


		this._getDonationJSON(type, function(obj) {  $this._renderPage(obj) });

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
		
		$.get(url, callback);
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
