/**
 * @author Ankit Patidar <ankitpatidar@hostnsoft.com> on 29/10/2013
 * @description It contains js code for admin panel
 */

/**
 * //change the heading for each page
 */
$('.headingName').click(function(){
    
    
    
    $('.headingTitle').html($(this).attr('title'));
    
    
});

/**
 * @added by Ankit Patidar <ankitpatidar@hostnsoft.com> on 30/10/2013
 * @param {string} call
 * @param {int} userId
 * @param {string} type
 * @returns {void}
 */
 function getDetails(call, userId, type)
    {
        
        //make request to get details
        $.ajax({
            url: "/controller/userCallLog.php",
            data: {call: call, 
                   userId: userId,
                   type: type},
            type: "post",
            dataType: "json",
            success: function(response)
            {    
                //define variables
                var divId,mainRsp,chartType,title,totalDuration;
                
                //check call and set response and div id and chart type and title
                if(call == 'userCallLogsForChart')
                {
                    mainRsp = response;
                    divId = '#getStatusDetailsBarGraphContainer';
                    chartType = 'column';
                    title = 'call log in bar chart';
                }
                else //for call status
                {
                    mainRsp = response.via;
                    
                    //get total duration
                    totalDuration = response.totalSumDuration;
                    
                    divId = '#getStatusDetailsPieGraphContainer';
                    chartType = 'pie';
                    title = 'call status in pie chart';
                }
                
                 //intialise arrays
                var categories = [];
                var dataArray = [];
                var mix = new Array();
                
                //counter
                var i = 0,val;
                
                
                //create array for categories and dataArray to set in chart plugin
                $.each(mainRsp,function(key,element){
                    
                    categories[i] = key;
                    
                    dataArray[i] = parseInt(element);
                    
                    if(totalDuration != undefined)
                    {
                        val = parseInt(element);
                        mix[i] = new Array(key,Math.round((val/totalDuration)*100)); //get value and percentage
                    }
                    i++; //increament counter
                    
                }); //end of each
                
                
                //take series array
                var series;
                
                //prepare series array call value wise
                if(call == 'userCallLogsForChart')
                   series= [{ //format for column
                        name: 'call status',
                        data:dataArray

                    }];
                else 
                   series= [{ //format for pie
                        name: 'call Status',
                        data: mix
                    }];
          
                //call function to draw graph in bar
                createHighchart(divId,chartType,title,series,categories);
        
           } //end of success function
     });
        
        
        
        
}//end of function 
    
 /**
  *@author Ankit patidar <ankitpatidar@hostnsoft.com>
  *@since 13/2/2014
  *function to get details for column graph creation
  */   
 function getCallWiseColumn(call,userId,type)
 {
     
     //get start date and end date from datepicker and pass in ajax request
       //make request to get details
        $.ajax({
            url: "/controller/userCallLog.php",
            data: {call: call, 
                   userId: userId,
                   type: type,
                   startDate:'',
                   endDate:''},
            type: "post",
            dataType: "json",
            success: function(response)
            {    
                //define variables
                var divId,mainRsp,chartType,title,totalDuration;
                
                if(response.status == 1)
                {
                    //check call and set response and div id and chart type and title
                    mainRsp = response.data;
                    divId = '#getStatusDetailsBarGraphContainer';
                    chartType = 'column';
                    title = 'call log in bar chart';

                    //intialise arrays
                    var categories = [];
                    var dataArray = [];
                    var mix = new Array();

                    //counter
                    var i = 0,val;
                
                    //create array for categories and dataArray to set in chart plugin
                    $.each(mainRsp,function(key,element){

                        categories[i] = element.date;

                        dataArray[i] = parseInt(element.count);

    //                    if(totalDuration != undefined)
    //                    {
    //                        val = parseInt(element);
    //                        mix[i] = new Array(key,Math.round((val/totalDuration)*100)); //get value and percentage
    //                    }
                        i++; //increament counter

                    }); //end of each

                    //take series array
                    var series;

                    //prepare series array call value wise

                    series= [{ //format for column
                            name: 'call status',
                            data:dataArray

                        }];

          
                    console.log(series);
                    //call function to draw graph in bar
                    createHighchart(divId,chartType,title,series,categories);
                }
           } //end of success function
     });
 }
    
 function getStatusForPie(call,userId,type)
 {
     //get start date and end date from datepicker and pass in ajax request
       //make request to get details
        $.ajax({
            url: "/controller/userCallLog.php",
            data: {call: call, 
                   userId: userId,
                   type: type,
                   startDate:'',
                   endDate:''},
            type: "post",
            dataType: "json",
            success: function(response)
            {    
                //define variables
                var divId,mainRsp,chartType,title,totalDuration;
                
                if(response.status == 1)
                {
                    mainRsp = response.data;

                    divId = '#getStatusDetailsPieGraphContainer';
                    chartType = 'pie';
                    title = 'call status in pie chart';

                    //intialise arrays
                    var categories = [];
                    var dataArray = [];
                    var mix = new Array();

                    //counter
                    var i = 0,val;


                    //create array for categories and dataArray to set in chart plugin
                    $.each(mainRsp,function(key,element){

                        categories[i] = element.status;

                        dataArray[i] = parseInt(element.count);
                        mix[i] = new Array(element.status,parseInt(element.count)); 
    //                    if(totalDuration != undefined)
    //                    {
    //                        val = parseInt(element);
    //                        mix[i] = new Array(key,Math.round((val/totalDuration)*100)); //get value and percentage
    //                    }
                        i++; //increament counter

                    }); //end of each


                    //take series array
                    var series;

                    //prepare series array status value wise
                    series= [{ //format for pie
                        name: 'call Status',
                        data: mix
                    }];

                    console.log(series);
                    //call function to draw graph in bar
                    createHighchart(divId,chartType,title,series,categories);
               }
           } //end of success function
     });
 }
    /**
     * @author Ankit Patidar <ankitpatidar@hostnsoft.com> on 31/10/2013
     * @param {string} id div id to show graph
     * @param {string} chartType
     * @param {string} Title title of the graph
     * @param {array} dataArray it contains series array for highchart plugin
     * @param {array} datas it contains categories
     * @returns {void}
     */
    function createHighchart(id,chartType,Title,dataArray,datas)
    {

        //jquery function to genrate graph    
        $(function () {
      
        //highchart function with proper setting to genrate graph           
        $(id).highcharts({
           
            chart: {
                type: chartType
              //  height:height,
               // width:width
            },
            title: {
                text: Title
            },
            
            xAxis: {
                categories: datas,
                title: {
                    text: null
                },
               labels:
                       {
                        rotation:-70,
                        align:'right'
                       }
            },
            yAxis: {
                min: 0,
                title: {
                   
                    align: 'high'
                },
                labels: {
                    overflow: 'justify'
                    
                }
            },
            tooltip: {
               // pointFormat: '{point.percentage}',
                //percentageDecimals:1,
                //valueDecimals:0
            
            },
            plotOptions: {
                bar: {
                    dataLabels: {
                        
                    }
                },
                column:{
                  pointWidth:5 //set column width  
                },
                pie:{
               allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: true,
                        color: '#000000',
                        connectorColor: '#000000',
                        
                         formatter: function() {
                            //console.log(this);
//                             if(cType == "amount")
//                                return '<b>'+ this.point.name +'</b>: '+this.y+' Rs';
//                            else
                                return '<b>'+this.point.name+'<b>:'+this.y;//+'%';
                            }
                        },
                     showInLegend: true
                }
            },
            legend: {
                //layout: 'vertical',
                //align: 'right',
                //verticalAlign: 'top',
                //x: 16,
                //y: 100,
               // floating: true,
               // borderWidth: 1,
                //backgroundColor: '#FFFFFF',
                //shadow: true
            },
            credits: {
                enabled: false
            },
             colors:[
                '#7591AD',
                '#B5C9DD',
                '#11C4A5', 
                '#F4C414', 
                '#FF5E5E', 
                '#CCCCC', 
                '#4C5D6C'
                
            ],
            series:dataArray
        });
    }); //end of plugin
    
        
    }
    
    
    function routePagination(count,strt,divs,routeId)
{
//     if(type == 1){
//         type ='batchId';
//     }else
//         type ='clientId';
//    
    if(strt == undefined || strt == 0 || strt== "")
        strt=1;
    
    if(count == undefined || count == 0 || count== "")
        count = 1;
    
    
        //code for pagination
	if(count > 1 ){
            
            
		$(divs).paginate({
			count       : count,
			start       : strt,
			display     : 10,
			border : false,
			text_color: '#000',
			background_color: '#ddd',
			text_hover_color: '#fff',
			background_hover_color: '#333',
			images                  : false,
			mouse                   : 'press',
			page_choice_display     : true,
			show_first              : true,
			show_last               : true,
			rotate					: false,
			item_count_display      : true,						
			item_count_total : count,
			onChange                : function(page){
                            
                         

                            if(routeId == undefined || routeId == null )
				window.location.href= window.location.href.split('?')[0]+'?pageNo='+page;
                            else
                               window.location.href= window.location.href.split('?')[0]+'?pageNo='+page+'&routeId='+routeId+'&tb=0';
                        }
                                    
		});
	}
        
} 