function tzFilterElementChange(element){
  updateUrl({name: element.name, value: element.value})
}

function updateUrl(item){
  var search = location.search.substring(1);
  const query = JSON.parse('{"' + decodeURI(search).replace(/"/g, '\\"').replace(/&/g, '","').replace(/=/g,'":"') + '"}')
  query[item.name] = item.value;
  query['paged'] = 1;
  if(!item.value){
    delete query[item.name];
  }
  let params = '?';
  let index = 0;
  Object.keys(query).forEach(e=>{
    if(index > 0){
      params += '&';
    }
    params += `${e}=${query[e]}`;
    index ++;
  });
  window.history.replaceState(location.pathname, document.title, location.pathname + params);
  location.reload();
}
const generateAuthKey = e=>{
  const authKey = document.getElementById('tz_auth_key');
  if(authKey){
    authKey.value = authKeyGenerator();
  }
}

function authKeyGenerator() {
  var length = 35,
      charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789@#$^&*()_-+=.<>,][{}|?",
      retVal = "";
  for (var i = 0, n = charset.length; i < length; ++i) {
      retVal += charset.charAt(Math.floor(Math.random() * n));
  }
  return retVal;
}