package mint

import (
	"net/http"
	"strconv"
	"github.com/gin-gonic/gin"
	"github.com/go-pg/pg/v10"
	"time"
	"encoding/json"

)
type ArticleList struct {
	Id     int `form:"id" json:"id"`
	CollectionId int `form:"collection_id" json:"collection_id" binding:"required"`
	ArticleId int `form:"article_id" json:"article_id" binding:"required"`
    CreatedAt time.Time
}
type ArticleListHolder struct{
	Items []ArticleList
}
func (i *ArticleListHolder) UnmarshalJSON(b []byte) error{
	return json.Unmarshal(b, &i.Items)
}
//查询
func GetCollectionArticleList(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {
		cid,err:= strconv.Atoi(c.Param("cid"))
		if err != nil {
			panic(err)
		}

		// TODO 在这里进行db操作
		// Select user by primary key.
		var articles []ArticleList
		err = db.Model(&articles).Column("collection_id","article_id").Where("collection_id = ?",cid).Select()
		if err != nil {
			panic(err)
		}
		
		c.JSON(http.StatusOK, gin.H{
			"message": articles,
		})
	}
}



//修改
func PostArticleListByArticle(db *pg.DB) gin.HandlerFunc{
	return func(c *gin.Context){
		aid,err:= strconv.Atoi(c.Param("aid"))
		if err != nil {
			panic(err)
		}
		//先删除
		_, err = db.Model((*ArticleList)(nil)).Where("article_id = ?",aid).Delete()
		if err != nil {
			panic(err)
		}

		var form ArticleListHolder
		if err := c.ShouldBindJSON(&form); err != nil {
			c.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
			return
		}

		tx, err := db.Begin()
		if err != nil {
			panic(err)
		}
		defer tx.Rollback()
		stmt, err := tx.Prepare("INSERT INTO article_lists( collection_id, article_id ) VALUES( $1, $2 )")
		if err != nil {
			panic(err)
		}
		defer stmt.Close()
		for _, value := range form.Items{
			_, err = stmt.Exec(value.CollectionId,aid)
			if err != nil {
				panic(err)
			}
	
		}
		err = tx.Commit()
		if err != nil {
			panic(err)
		}
		c.JSON(http.StatusOK,gin.H{
			"message":"update ok",
		})
	}
}


