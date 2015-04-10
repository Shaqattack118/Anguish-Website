/**
 * Donation Page Javascript,
 * TODO: Shopping cart and such validation 
 */
var AnguishDonationPage = new function AnguishDonationPage() 
{
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
		this._renderPage(this._getDonationJSON('2'));
	}
	
	/**
	 * Create Table Row based on json structure 
	 */
	this._createRows = function (tableData){
	
		var table = $('.donationTable');
		
		table.find(".row").remove(); // remove all .rows since we are redrawing
		
		_.each(tableData, function(obj){
		
		var row = create("tr").addClass("row");
			
			row.append(create("td").addClass("image").append(create("img").attr("src", obj.image)));
			row.append(create("td").addClass("name").append(obj.name));
			row.append(create("td").addClass("cost").append(obj.cost));
			row.append(create("td").addClass("cost").append(create("a").addClass("button small").append("buy")));
			table.append(row);
		});
		
	};
	
	/**
	 * Tab click event
	 */
	this._tabClickEvent = function(e){
	
		var target = $(e.currentTarget);
		var type = target.attr("type");
		
		/** Remove active as we are switching tabs **/
		$(".tab-links").find(".active").removeClass("active");
		
		target.addClass("active");

		/** get new data to render **/
		var data = this._getDonationJSON(type);
		
		/** Render section **/
		this._renderPage(data);
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
	this._getDonationJSON = function(type){
	
		switch(type){
			case '0':
				return [{'image': 'http://rigory.com/forums/public/style_images/donatoritems/01%20-%20LHc77tM.png','name': 'Donator Pin','cost': '25','itemid': '0','amt': '1'},{'image': 'http://rigory.com/forums/public/style_images/donatoritems/02%20-%20aXHQyLB.png','name': 'Super Donator Pin','cost': '15','itemid': '0','amt': '1'},{'image': 'http://rigory.com/forums/public/style_images/donatoritems/01%20-%20LHc77tM.png','name': 'Donator Pin Pack (5)','cost': '100','itemid': '0','amt': '5'},{'image': 'http://rigory.com/forums/public/style_images/donatoritems/03%20-%20OmVjtMd.png','name': '15% increased Drop Rate Pin','cost': '20','itemid': '0','amt': '1'}];

			case '1':
			return [{'image': 'http://www.nearreality.com/addons/static/41.png','name': 'Steadfast Boots','cost': '20','itemid': '21787','amt': '1'},{'image': 'http://www.nearreality.com/addons/static/61.png','name': 'Glaiven Boots','cost': '10','itemid': '21790','amt': '1'},{'image': 'http://www.nearreality.com/addons/static/18.png','name': 'Ragefire Boots','cost': '10','itemid': '21793','amt': '1'},{'image': 'http://www.nearreality.com/addons/static/20.png','name': 'New Fire Cape','cost': '20','itemid': '23639','amt': '1'},{'image': 'http://www.nearreality.com/addons/static/62.png','name': 'Ganodermic Set','cost': '45','itemid': '0','amt': '1'},{'image': 'http://www.nearreality.com/addons/static/52.png','name': 'Primal Set','cost': '100','itemid': '0','amt': '1'}];
			
			case '2':
			return [{'image': 'http://www.nearreality.com/addons/static/9.png','name': 'Hand Cannon','cost': '40','itemid': '15241','amt': '1'},{'image': 'http://www.nearreality.com/addons/static/96.png','name': 'Armadyl God Sword','cost': '35','itemid': '13450','amt': '1'},{'image': 'http://www.nearreality.com/addons/static/87.png','name': 'Statius Warhammer','cost': '30','itemid': '13902','amt': '1'},{'image': 'http://www.nearreality.com/addons/static/88.png','name': 'Zuriels Staff','cost': '30','itemid': '13867','amt': '1'},{'image': 'http://www.nearreality.com/addons/static/86.png','name': 'Vestas Longsword','cost': '30','itemid': '13889','amt': '1'},{'image': 'http://www.nearreality.com/addons/static/48.png','name': 'Rune Defender','cost': '5','itemid': '8850','amt': '1'}];
			case '3':
			return  [{'image': 'http://rigory.com/forums/public/style_images/donatoritems/17%20-%20uN0SKxt.png','name': 'Orange H’ween','cost': '70','itemid': '0','amt': '1'},{'image': 'http://rigory.com/forums/public/style_images/donatoritems/18%20-%20gpr6Owp.png','name': 'Chompy Bird Hat','cost': '20','itemid': '2978','amt': '1'},{'image': 'http://rigory.com/forums/public/style_images/donatoritems/19%20-%20QZCCnlL.png','name': 'Frost Dragon Mask','cost': '25','itemid': '19295','amt': '1'},{'image': 'http://rigory.com/forums/public/style_images/donatoritems/20%20-%20ll8Gz4A.gif','name': 'Random Hween Mask','cost': '20','itemid': '0','amt': '1'}];		
		}
		
		console.log(type);
	};
	
	/**
	* Render the page
	*/
	this._renderPage = function(json){
		this._createRows(json);
	}
	
	return AnguishDonationPage;
}
