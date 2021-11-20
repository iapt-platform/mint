//index
//http://localhost/mint/v2/public/api/courses

//show
//http://localhost/mint/v2/public/api/courses/2

//stor
let data = {title:"visuddhimagga"};
fetch("http://localhost/mint/v2/public/api/courses",{
    body:JSON.stringify(data),
    headers:{
        'content-type':'application/json'
    },
    method:'POST'
}).then(response =>response.json());

//update
fetch("http://localhost/mint/v2/public/api/courses/1?title=newtitle1",{
    headers:{
        'content-type':'application/json'
    },
    method:'PUT'
}).then(response =>response.json());

//delete
fetch("http://localhost/mint/v2/public/api/courses/1",{
    headers:{
        'content-type':'application/json'
    },
    method:'DELETE'
}).then(response =>response.json());

