# API In V1

```javascript
/**
 * /api/v2/like
 * @method POST
 * @param {string} liketype 下列值之一 like,favorite,watch
 * @param {string} restype 资源类型 下列值之一 chapter,article,course
 * @param {string} resid   资源 uuid
 *
 * @responce {json}
 *          data{
                ok: bool,
                message: string,
                data:{
                    type: string, 资源类型 下列值之一 chapter,article,course
                    target_id: string, 资源 uuid
                    id: string 点赞记录的uuid
                }
            }
 */
function add(liketype, restype, resid) {
    fetch('/api/v2/like', {
        method: 'POST',
        credentials: 'include',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            type: liketype,
            target_type: restype,
            target_id: resid
        })
    })
        .then(response => response.json())
        .then(function (data) {
            console.log(data);
            let result = data.data;
            if (data.ok == true) {
                for (let it of arrElement) {
                    if (result.type === it.like_type &&
                        result.target_id === it.resource_id) {
                        it.like++;
                        it.me = result.id;
                    }
                }
                Render();
            }
        });
}
```
