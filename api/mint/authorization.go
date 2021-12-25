package mint

import (
	"net/http"
	"strconv"
	"time"

	"github.com/gin-gonic/gin"
	"github.com/go-pg/pg/v10"
)

type Authorization struct {
	Id int `form:"id" json:"id" binding:"required"`

	ResourceId   int    `form:"resource_id" json:"resource_id"`
	ResourceType string `form:"resource_type" json:"resource_type"`

	UserId   string `form:"user_id" json:"user_id"`
	UserType string `form:"user_type" json:"user_type"`

	ResRight string `form:"res_right" json:"res_right"`
	OwnerId  int    `form:"owner_id" json:"owner_id"`

	ExpiredAt  time.Time `form:"expired_at" json:"expired_at"`
	AcceptedAt time.Time `form:"accepted_at" json:"accepted_at"`

	Version int

	CreatedAt time.Time
	UpdatedAt time.Time
}

//查询display a list of all Authorizations
func AuthorizationsIndex(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {
		//按照标题搜索，或者按照authorization列出子课程
		resource_id := c.Query("resource_id")
		resource_type := c.Query("resource_type")

		// TODO 在这里进行db操作
		// Select user by primary key.
		var authorizations []Authorization

		err := db.Model(&authorizations).Column("id", "resource_id", "resource_type", "user_id", "user_type", "res_right", "expired_at", "accepted_at").Where("resource_id = ?", resource_id).Where("resource_type = ?", resource_type).Select()
		if err != nil {
			panic(err)
		}

		c.JSON(http.StatusOK, gin.H{
			"message": authorizations,
		})
	}
}

//return an HTML form for creating a new Authorizations
func AuthorizationsNew(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {
		//TODO 业务逻辑
		c.HTML(http.StatusOK, "authorizations/new.html", gin.H{
			"message": "ok",
		})
	}
}

//新建create a new Authorizations
func AuthorizationsCreate(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {

		var form Channel

		if err := c.ShouldBindJSON(&form); err != nil {
			c.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
			return
		}
		_, err := db.Model(&form).Insert()
		if err != nil {
			panic(err)
		}

		c.JSON(http.StatusOK, gin.H{
			"status": "sucess",
			"data":   form,
		})
	}
}

//查询display a specific Authorizations
func AuthorizationsShow(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {
		id, err := strconv.Atoi(c.Param("id"))
		if err != nil {
			panic(err)
		}
		authorization := Authorization{Id: id}
		err = db.Model(&authorization).WherePK().Select()

		if err != nil {
			panic(err)
		}
		c.JSON(http.StatusOK, gin.H{
			"status": "sucess",
			"data":   authorization,
		})

	}
}

//return an HTML form for edit a Authorizations
func AuthorizationsEdit(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {
		//TODO 业务逻辑
		c.HTML(http.StatusOK, "authorizations/edit.html", gin.H{
			"message": "ok",
		})
	}
}

//update a specific Authorizations

func AuthorizationsUpdate(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {
		var form Authorization

		if err := c.ShouldBindJSON(&form); err != nil {
			c.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
			return
		}

		_, err := db.Model(&form).Column("res_right").WherePK().Update()
		if err != nil {
			panic(err)
		}
		c.JSON(http.StatusOK, gin.H{
			"message": "update ok",
		})
	}
}

//删
//delete a specific Authorizations
func AuthorizationsDestroy(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {
		id, err := strconv.Atoi(c.Param("id"))
		if err != nil {
			panic(err)
		}
		authorization := &Authorization{
			Id: id,
		}

		_, err = db.Model(authorization).WherePK().Delete()
		if err != nil {
			panic(err)
		}

		c.JSON(http.StatusOK, gin.H{
			"message": c.Param("id"),
		})
	}
}
