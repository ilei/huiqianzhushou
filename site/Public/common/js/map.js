/**
 * 
 */
//百度地图API功能
var map = new BMap.Map("mapzoom");
var point = new BMap.Point(117.217415,39.145267);
map.centerAndZoom(point,15);
map.enableScrollWheelZoom(); 
//获取当前城市中心点
function myFun(result){
   	var cityName = result.name;
   	map.setCenter(cityName);
   	$('input[name=city]').val(cityName);
}
var myCity = new BMap.LocalCity();
myCity.get(myFun);
//显示经纬度
map.addEventListener("click",function(e){
    $('input[name=lng]').val(e.point.lng);
   	$('input[name=lat]').val(e.point.lat);
});

var local = new BMap.LocalSearch(map, {
   	  renderOptions:{map:map}
 });
local.setPageCapacity(15);
$('.js-search-map').click(function(){
	local.search($('input[name=keyword]').val());
});
