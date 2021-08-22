package mint

import (
	"encoding/json"
	"github.com/gin-gonic/gin"
	"github.com/go-pg/pg/v10"
	"github.com/go-redis/redis/v8"
	"net/http"
	"strconv"
	"time"
)

type ArticleList struct {
	Id           int `form:"id" json:"id"`
	CollectionId int `form:"collection_id" json:"collection_id" binding:"required"`
	ArticleId    int `form:"article_id" json:"article_id" binding:"required"`
	CreatedAt    time.Time
}
type ArticleListHolder struct {
	Items []ArticleList
}

func (i *ArticleListHolder) UnmarshalJSON(b []byte) error {
	return json.Unmarshal(b, &i.Items)
}

//查询
func GetCollectionArticleList(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {
		cid, err := strconv.Atoi(c.Param("cid"))
		if err != nil {
			panic(err)
		}

		// TODO 在这里进行db操作
		// Select user by primary key.
		var articles []ArticleList
		err = db.Model(&articles).Column("collection_id", "article_id").Where("collection_id = ?", cid).Select()
		if err != nil {
			panic(err)
		}

		c.JSON(http.StatusOK, gin.H{
			"message": articles,
		})
	}
}

//修改
func PostArticleListByArticle(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {
		aid, err := strconv.Atoi(c.Param("aid"))
		if err != nil {
			panic(err)
		}
		//先删除
		_, err = db.Model((*ArticleList)(nil)).Where("article_id = ?", aid).Delete()
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
		for _, value := range form.Items {
			_, err = stmt.Exec(value.CollectionId, aid)
			if err != nil {
				panic(err)
			}

		}
		err = tx.Commit()
		if err != nil {
			panic(err)
		}
		c.JSON(http.StatusOK, gin.H{
			"message": "update ok",
		})
	}
}

//删
func DeleteArticleInList(db *pg.DB, rdb *redis.Client) gin.HandlerFunc {
	return func(c *gin.Context) {
		id, err := strconv.Atoi(c.Param("aid"))
		if err != nil {
			panic(err)
		}
		//删之前获取 course_id
		_, err = db.Model((*ArticleList)(nil)).Where("article_id = ?", id).Delete()
		if err != nil {
			panic(err)
		}
		//TODO 删除article_list表相关项目
		c.JSON(http.StatusOK, gin.H{
			"message": "delete " + c.Param("aid"),
		})

		rkey := "article_list://" + c.Param("aid")
		rdb.Del(ctx, rkey)

	}
}

//删
func DeleteCollectionInList(db *pg.DB, rdb *redis.Client) gin.HandlerFunc {
	return func(c *gin.Context) {
		id, err := strconv.Atoi(c.Param("cid"))
		if err != nil {
			panic(err)
		}
		//删之前获取 course_id
		_, err = db.Model((*ArticleList)(nil)).Where("collection_id = ?", id).Delete()
		if err != nil {
			panic(err)
		}
		//TODO 删除article_list表相关项目
		c.JSON(http.StatusOK, gin.H{
			"message": "delete " + c.Param("cid"),
		})

		rkey := "article_list://collection_" + c.Param("cid")
		rdb.Del(ctx, rkey)
	}
}
