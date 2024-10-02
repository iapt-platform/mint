package mint

import (
	"net/http"
	"strconv"
	//"fmt"
	"github.com/gin-gonic/gin"
	"github.com/go-pg/pg/v10"
	"time"
)

type Collection struct {
	Id     int `form:"id" json:"id" `
	Uid string `form:"uid" json:"uid" `
	ParentId int `form:"parent_id" json:"parent_id"`
	PrParentId int `form:"pr_parent_id" json:"pr_parent_id" `

	Title string `form:"title" json:"title" `
	Subtitle string `form:"subtitle" json:"subtitle" `
	Summary string `form:"summary" json:"summary"`
	
	ArticleList string `form:"article_list" json:"article_list" `
	
	Lang string `form:"lang" json:"lang" `
	Setting string `form:"setting" json:"setting" `
	Status string `form:"status" json:"status" `

	OwnerId int
	Version int
    DeletedAt time.Time
    CreatedAt time.Time
    UpdatedAt time.Time
}
//查询
	//display a list of all collections
func CollectionsIndex(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {
		title:= c.Param("ctitle")

		// TODO 在这里进行db操作
		// Select user by primary key.
		var collections []Collection
		err := db.Model(&collections).Column("id","title","subtitle").Where("title like ?",title+"%").Select()
		if err != nil {
			panic(err)
		}
		
		c.JSON(http.StatusOK, gin.H{
			"message": collections,
		})
	}
}

//查询
	//display a specific collections

func CollectionsShow(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {
		id,err := strconv.Atoi(c.Param("id"))
		if err != nil {
			panic(err)
		}
		// TODO 在这里进行db操作
		// Select user by primary key.
		collection := &Collection{Id: id}
		err = db.Model(collection).Column("title","subtitle","summary","status").WherePK().Select()
		if err != nil {
			panic(err)
		}
		
		c.JSON(http.StatusOK, gin.H{
			"message": collection,
		})
	}
}

//return an HTML form for creating a new Courses
func CollectionsNew(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {
		//TODO 业务逻辑
		c.HTML(http.StatusOK,"collections/new.html",gin.H{
			"message":"ok",
		})
	}
}

//新建-
	//create a new collections

func CollectionsCreate(db *pg.DB) gin.HandlerFunc{
	return func(c *gin.Context){
	
		title := c.Query("title")
		status :=c.Query("status")

		newOne := &Collection{
			Title:   title,
			Status: status,
			OwnerId:1,
		}
		_, err := db.Model(newOne).Insert()
		if err != nil {
			panic(err)
		}

		//修改完毕
		c.JSON(http.StatusOK,gin.H{
			"message":"insert ok",
		})
	}
}
//return an HTML form for edit a Courses
func CollectionsEdit(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {
		//TODO 业务逻辑
		c.HTML(http.StatusOK,"collections/edit.html",gin.H{
			"message":"ok",
		})
	}
}



//改
//update a specific collections
func CollectionsUpdate(db *pg.DB) gin.HandlerFunc{
	return func(c *gin.Context){
		var form Collection

		if err := c.ShouldBindJSON(&form); err != nil {
			c.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
			return
		}

		_,err := db.Model(&form).Column("title","subtitle","summary","status","article_list").WherePK().Update()
		if err != nil {
			panic(err)
		}
		c.JSON(http.StatusOK,gin.H{
			"message":form,
		})
	}
}


//删
//delete a specific collections
func CollectionsDestroy(db *pg.DB) gin.HandlerFunc{
	return func(c *gin.Context){
		id,err := strconv.Atoi(c.Param("id"))
		if err != nil {
			panic(err)
		}
		collection := &Collection{
			Id:int(id),
		}

		_, err = db.Model(collection).WherePK().Where("parent_id = ?",id).Where("pr_parent_id = ?",id).Delete()
		if err != nil {
			panic(err)
		}
		//TODO redis delete
		
		c.JSON(http.StatusOK,gin.H{
			"message":c.Param("id"),
		})
	}
}