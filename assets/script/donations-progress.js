let progressBars;
jQuery(document).ready($=> {
  progressBars = document.getElementsByClassName('tz_pg_progress_bar_container');
  for(let i = 0; i < progressBars.length; i++) {
    getDonationProgress(progressBars[i].getAttribute('data-id'), i, (data,index)=>{
      if(data.shouldRender){
        console.log(index);
        console.log(data);
        const content = `
            <div style="display: flex; justify-content-between; font-weight: bold">
            <div style="width: 100%;">${tzFormatCurrency(data.total, data.currency)}<small style="color: ${data.bg}">${data.currency}</small></div>
            <div style="width: 100%; text-align: right">${tzFormatCurrency(data.target, data.currency)}<small style="color: ${data.bg}">${data.currency}</small></div>
          </div>  
          <div style="position: relative; background-color: #ccc9; border-radius: 5px; overflow: hidden; height: 18px">
            <div style="transition: 11.5s all ease-in; height: 18px; background-color: ${data.bg}; position: absolute; width:${data.percentage> 100? 100: data.percentage}%; color: transparent">.</div>
            <div style="height: 18px; line-height: 18px; text-shadow: 1px 1px 2px black; color: #fff; width: 100%; position: absolute; text-align: center;">${data.percentage}%</div>
          </div>
        `;
        progressBars[i].innerHTML = content;
      }
    });
  }

})


const getDonationProgress = async function (id, index ,callback){
  if(!id) return;
  try{
    
      const formDataObj = {id};
      
      await jQuery.post(`${tzProgress.siteUrl}/wp-json/tranzak-payment-gateway/v1/donation/get-progress`, formDataObj, function(res) {
        if(res && res.success){
          callback(res.data, index);
        };
        
      }).fail(function(){

      });

  }catch(e){
    
  }


}


function tzFormatCurrency (value, currency = 'XAF'){
  return new Intl.NumberFormat(lang, { style: 'currency', currency: currency }).formatToParts(value).map(
      p => p.type != 'literal' && p.type != 'currency' ? p.value : ''
  ).join('');
}
