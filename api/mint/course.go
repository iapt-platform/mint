package mint

import (
	"net/http"
	"strconv"
	"fmt"
	"github.com/gin-gonic/gin"
	"github.com/go-pg/pg/v10"
)

type Course struct{
    Id int64
    Cover string
    Title string
    Subtitle string
    Summary string
    Teacher int64
    Tag string
    Lang string
    Speech_lang string
    Status int64
    Lesson_num int
    Creator int64
    Create_time int64
    Update_time int64
    Delete_time int64
    Content string
}

//查询
func GetCourse(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {
		cid,err := strconv.ParseInt(c.Param("cid"),10,64)
		if err != nil {
			panic(err)
		}
		fmt.Println("get course")
		// TODO 在这里进行db操作
		// Select user by primary key.
		course := &Course{Id: cid}
		err = db.Model(course).WherePK().Select()
		if err != nil {
			panic(err)
		}
		
		c.JSON(http.StatusOK, gin.H{
			"message": "course-"+course.Title,
		})
	}
}

//查询
func GetCourseByTitle(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {
		ctitle:= c.Param("ctitle")

		// TODO 在这里进行db操作
		// Select user by primary key.
		var courses []Course
		err := db.Model(&courses).Column("id","title","subtitle").Where("title like ?",ctitle+"%").Select()
		if err != nil {
			panic(err)
		}
		
		c.JSON(http.StatusOK, gin.H{
			"message": courses,
		})
	}
}

//增加
func PutCourse(db *pg.DB) gin.HandlerFunc{
	return func(c *gin.Context){
		title := c.Query("title")
		status1,err := strconv.ParseInt(c.Query("status"),10,64)
		if err != nil {
			panic(err)
		}
		fmt.Println("title:"+title)

		newCouse := &Course{
			Title:   title,
			Status: status1,
			Teacher:1,
			Creator:1,
			Create_time:1,
		}
		_, err = db.Model(newCouse).Insert()
		if err != nil {
			panic(err)
		}
		c.JSON(http.StatusOK,gin.H{
			"message":"new-Ok- hello "+title,
		})
	}
}

//改
func PostCourse(db *pg.DB) gin.HandlerFunc{
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