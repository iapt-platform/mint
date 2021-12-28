package mint

import (
	"context"
	"encoding/json"
	"fmt"
	"net/http"
	"strconv"
	"time"

	"github.com/gin-gonic/gin"
	"github.com/go-pg/pg/v10"
	"github.com/go-redis/redis/v8"
)

var ctx = context.Background()

type Article struct {
	Id          int    `json:"id" `
	Uid         string `json:"uid" `
	ParentId    int    `json:"parent_id"`
	PrParentId  int    `json:"pr_parent_id" `
	Title       string `json:"title"`
	Subtitle    string `json:"subtitle"`
	Summary     string `json:"summary"`
	Content     string `json:"content"`
	ContentType string `json:"content_type"`
	Lang        string `json:"lang"`
	Setting     string `json:"setting"`
	Status      string `json:"status"`
	EditorId    int    `json:"editor_id"`
	StudioId    int    `json:"studio_id"`
	CreatorId   int    `json:"creator_id"`
	Version     int
	DeletedAt   time.Time
	CreatedAt   time.Time
	UpdatedAt   time.Time
}

//查询
/*
parent_id=0 的文章为文章模板
parent_id>0 的文章为文章实例，是模板+channel(多个)
*/
//查询 列表
func ArticlesIndex(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {
		view := c.Query("view")
		studio := c.DefaultQuery("studio", "")
		status := c.DefaultQuery("status", "public")

		columns := "id,title,subtitle,owner_id"
		var articles []Article
		var err error
		if studio == "" {
			switch view {
			case "title":
				err = db.Model(&articles).ColumnExpr(columns).Where("title like ?", c.Query("view")+"%").Select()
			case "tempalet":
				//列出文章模板
				err = db.Model(&articles).ColumnExpr(columns).Where("parent_id = 0").Select()
			case "instance":
				//文章实例
				err = db.Model(&articles).ColumnExpr(columns).Where("parent_id = ?", c.Query("id")).Select()
			case "pr":
				//修改建议
				err = db.Model(&articles).ColumnExpr(columns).Where("pr_parent_id = ?", c.Query("id")).Select()
			default:
				panic("error view")
			}

		} else {
			if status == "all" {
				//TODO studio 鉴权
				switch view {
				case "title":
					err = db.Model(&articles).ColumnExpr(columns).Where("title like ?", c.Query("view")+"%").Select()
				case "tempalet":
					//列出文章模板
					err = db.Model(&articles).ColumnExpr(columns).Where("parent_id = 0").Select()
				case "instance":
					//文章实例
					err = db.Model(&articles).ColumnExpr(columns).Where("parent_id = ?", c.Query("id")).Select()
				case "pr":
					//修改建议
					err = db.Model(&articles).ColumnExpr(columns).Where("pr_parent_id = ?", c.Query("id")).Select()
				default:
					panic("error view")
				}
			} else {
				//TODO studio 鉴权
				switch view {
				case "title":
					err = db.Model(&articles).ColumnExpr(columns).Where("title like ?", c.Query("view")+"%").Where("status = ?", "public").Select()
				case "tempalet":
					//列出文章模板
					err = db.Model(&articles).ColumnExpr(columns).Where("parent_id = 0").Where("status = ?", "public").Select()
				case "instance":
					//文章实例
					err = db.Model(&articles).ColumnExpr(columns).Where("parent_id = ?", c.Query("id")).Where("status = ?", "public").Select()
				case "pr":
					//修改建议
					err = db.Model(&articles).ColumnExpr(columns).Where("pr_parent_id = ?", c.Query("id")).Where("status = ?", "public").Select()
				default:
					panic("error view")
				}
			}
		}
		if err != nil {
			panic(err)
		}

		c.JSON(http.StatusOK, gin.H{
			"status": "success",
			"data":   articles,
		})
	}
}

//return an HTML form for creating a new Courses
func ArticlesNew(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {
		//TODO 业务逻辑
		c.HTML(http.StatusOK, "articles_new.html", gin.H{
			"message": "ok",
		})
	}
}

//新建-
//create a new articles
func ArticlesCreate(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {

		var form Course

		if err := c.ShouldBindJSON(&form); err != nil {
			c.JSON(http.StatusBadRequest, gin.H{
				"ok":    false,
				"error": err.Error(),
			})
			return
		}

		if form.Title == "" {
			c.JSON(http.StatusBadRequest, gin.H{
				"ok":    false,
				"error": ":no-title",
			})
			return
		}

		//TODO 获取 userid
		form.EditorId = 1
		form.CreatorId = 1

		//TODO studio 鉴权

		_, err := db.Model(&form).Column("title", "status", "editor_id", "creator_id", "studio_id").Insert()
		if err != nil {
			panic(err)
		}

		//建立成功
		c.JSON(http.StatusOK, gin.H{
			"ok":   true,
			"data": form,
		})
	}
}

//display a specific articles
func ArticlesShow(db *pg.DB, rdb *redis.Client) gin.HandlerFunc {
	return func(c *gin.Context) {
		id, err := strconv.Atoi(c.Param("id"))
		if err != nil {
			panic(err)
		}

		rkey := "article/:id"
		n, err := rdb.HExists(ctx, rkey, c.Param("id")).Result()
		if err == nil && n {
			val, err := rdb.HGet(ctx, rkey, c.Param("id")).Result()
			if err == nil {
				var redisData Article
				json.Unmarshal([]byte(val), &redisData)
				c.JSON(http.StatusOK, gin.H{
					"ok":   true,
					"data": redisData,
				})
				return
			} else {
				fmt.Println(err)
			}
		} else {
			fmt.Println(err)
		}

		article := &Article{Id: id}
		err = db.Model(article).Column("id", "uid", "parent_id", "title", "subtitle", "content", "content_type", "lang", "owner_id", "setting", "status", "version", "updated_at").WherePK().First()
		if err != nil {
			if err.Error() == pg.ErrNoRows.Error() {
				c.JSON(http.StatusOK, gin.H{
					"ok":      true,
					"message": "no-rows",
				})
			} else {
				panic(err)
			}
		} else {
			c.JSON(http.StatusOK, gin.H{
				"ok":   true,
				"data": article,
			})
			//写入redis
			jsonData, err := json.Marshal(article)
			if err == nil {
				rdb.HSet(ctx, rkey, c.Param("id"), string(jsonData))
			}
		}

	}
}

//return an HTML form for edit a Courses
func ArticlesEdit(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {
		//TODO 业务逻辑
		c.HTML(http.StatusOK, "articles/edit.html", gin.H{
			"message": "ok",
		})
	}
}

//修改
//update a specific articles
func ArticlesUpdate(db *pg.DB, rdb *redis.Client) gin.HandlerFunc {
	return func(c *gin.Context) {
		var form Article

		if err := c.ShouldBindJSON(&form); err != nil {
			c.JSON(http.StatusBadRequest, gin.H{
				"ok":      true,
				"message": err.Error(),
			})
			return
		}
		if form.Title == "" {
			c.JSON(http.StatusBadRequest, gin.H{
				"ok":    false,
				"error": ":no-title",
			})
			return
		}

		//TODO 鉴权
		_, err := db.Model(&form).Column("title", "subtitle", "summary", "status", "content").WherePK().Update()
		if err != nil {
			panic(err)
		}
		c.JSON(http.StatusOK, gin.H{
			"status": "sucess",
			"data":   form,
		})
		rkey := "article/:id"
		rdb.Del(ctx, rkey, c.Param("id"))
	}
}

//删
//delete a specific articles
func ArticlesDestroy(db *pg.DB, rdb *redis.Client) gin.HandlerFunc {
	return func(c *gin.Context) {
		id, err := strconv.Atoi(c.Param("id"))
		if err != nil {
			panic(err)
		}
		article := &Article{
			Id: int(id),
		}
		//TODO 鉴权
		_, err = db.Model(article).WherePK().Where("parent_id = ?", id).Where("pr_parent_id = ?", id).Delete()
		if err != nil {
			panic(err)
		}

		rkey := "article://id"
		rdb.Del(ctx, rkey, c.Param("id"))

		//TODO 删除article_list表相关项目
		c.JSON(http.StatusOK, gin.H{
			"ok":   true,
			"data": c.Param("id"),
		})

	}
}
