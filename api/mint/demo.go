package mint

import (
	"net/http"
	"strconv"
	"fmt"
	"github.com/gin-gonic/gin"
	"github.com/go-pg/pg/v10"
)

type Login struct {
	User     string `form:"user" json:"user" binding:"required"`
	Password string `form:"password" json:"password" binding:"required"`
}

type User struct{
    Id     int64
    Name   string
    Emails []string
}
func LoginDemo(_db *pg.DB) gin.HandlerFunc {
		
	return func(c *gin.Context) {
		// TODO 在这里进行db操作
		var form Login

		if err := c.ShouldBindJSON(&form); err != nil {
			c.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
			return
		}
		if form.User != "manu" || form.Password != "123" {
			c.JSON(http.StatusUnauthorized, gin.H{"status": "unauthorized"})
			return
		}

		c.JSON(http.StatusOK, gin.H{
			"message": "pong",
		})
	}
}

//查询
func GetDemo(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {
		userid,err := strconv.ParseInt(c.Param("id"),10,64)
		if err != nil {
			panic(err)
		}
		fmt.Println("get demo")
		// TODO 在这里进行db操作
		// Select user by primary key.
		user := &User{Id: userid}
		err = db.Model(user).WherePK().Select()
		if err != nil {
			panic(err)
		}
		
		c.JSON(http.StatusOK, gin.H{
			"message": "user-"+user.Name,
		})
	}
}
func PostDemo(db *pg.DB) gin.HandlerFunc{
	return func(c *gin.Context){
		name := c.DefaultQuery("name","guest")
		password := c.Query("password")
		c.JSON(http.StatusOK,gin.H{
			"message": "name="+name+" password="+password,
		})
	}
}

//增加
func PutDemo(db *pg.DB) gin.HandlerFunc{
	return func(c *gin.Context){
		name := c.Query("name")
		email := c.Query("emails")

		user1 := &User{
			Name:   name,
			Emails: []string{email},
		}
		_, err := db.Model(user1).Insert()
		if err != nil {
			panic(err)
		}
		c.JSON(http.StatusOK,gin.H{
			"message":"new-Ok- hello "+name,
		})
	}
}
//改
func PatchDemo(db *pg.DB) gin.HandlerFunc{
	return func(c *gin.Context){
		userid,err := strconv.ParseInt(c.Param("id"),10,64)
		if err != nil {
			panic(err)
		}
		email := c.Query("emails")
		user1 := &User{
			Id:   userid,
			Emails: []string{email},
		}
		//_, err = db.Model(user1).WherePK().Update()
		_, err = db.Model(user1).Set("emails = ?emails").Where("id = ?id").Update()
		if err != nil {
			panic(err)
		}
		c.JSON(http.StatusOK,gin.H{
			"message":"-patch="+email,
		})
	}
}
//删
func DeleteDemo(db *pg.DB) gin.HandlerFunc{
	return func(c *gin.Context){
		userid,err := strconv.ParseInt(c.Param("id"),10,64)
		if err != nil {
			panic(err)
		}
		user1 := &User{
			Id:   userid,
		}
		_, err = db.Model(user1).Where("id = ?", userid).Delete()
		if err != nil {
			panic(err)
		}
		c.JSON(http.StatusOK,gin.H{
			"message":"delete "+c.Param("id"),
		})
	}
}