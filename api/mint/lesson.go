package mint

import (
	"net/http"
	"io/ioutil"
	"strings"
	"strconv"
	"fmt"
	"github.com/gin-gonic/gin"
	"github.com/go-pg/pg/v10"
	"time"
)

type Lesson struct {
	Id     int `form:"id" json:"id" binding:"required"`
	CourseId     int `form:"course_id" json:"course_id" binding:"required"`
	Cover string
	Title string `form:"title" json:"title" binding:"required"`
	Subtitle string `form:"subtitle" json:"subtitle" binding:"required"`
	Summary string `form:"summary" json:"summary" binding:"required"`
	Teacher int `form:"teacher" json:"teacher" binding:"required"`
	Lang string `form:"lang" json:"lang" binding:"required"`
	Speech_lang string `form:"speech_lang" json:"speech_lang" binding:"required"`
	Status int `form:"status" json:"status" binding:"required"`
	Content string `form:"content" json:"content" binding:"required"`
	Creator int
	StartDate time.Time `form:"date" json:"date" binding:"required"`
    Duration int64 `form:"duration" json:"duration" binding:"required"` 
	Version int64
    CreatedAt time.Time
    UpdatedAt time.Time
}
//查询
func GetLesson(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {
		lid,err := strconv.ParseInt(c.Param("lid"),10,64)
		if err != nil {
			panic(err)
		}
		fmt.Println("get lesson")
		// TODO 在这里进行db操作
		// Select user by primary key.
		lesson := &Lesson{Id: int(lid)}
		err = db.Model(lesson).WherePK().Select()
		if err != nil {
			panic(err)
		}
		
		c.JSON(http.StatusOK, gin.H{
			"message": "lesson-"+lesson.Title,
		})
	}
}

//查询
func GetLessonByTitle(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {
		title:= c.Param("ltitle")

		// TODO 在这里进行db操作
		// Select user by primary key.
		var lessons []Lesson
		err := db.Model(&lessons).Column("id","title","subtitle").Where("title like ?",title+"%").Select()
		if err != nil {
			panic(err)
		}
		
		c.JSON(http.StatusOK, gin.H{
			"message": lessons,
		})
	}
}

//新建-
//PUT http://127.0.0.1:8080/api/lesson?title=lesson-one&course_id=1&status=10
func PutLesson(db *pg.DB) gin.HandlerFunc{
	return func(c *gin.Context){
	
		title := c.Query("title")
		courseId,err := strconv.ParseInt(c.Query("course_id"),10,64)
		status1,err := strconv.ParseInt(c.Query("status"),10,64)
		if err != nil {
			panic(err)
		}

		newLesson := &Lesson{
			Title:   title,
			CourseId: int(courseId),
			Status: int(status1),
			Teacher:0,
			Creator:1,
		}
		_, err = db.Model(newLesson).Insert()
		if err != nil {
			panic(err)
		}

		//查询这个course 里面有几个课程
		countLesson, err := db.Model((*Lesson)(nil)).Where("course_id = ?",courseId).Count()
		if err != nil {
			panic(err)
		}		
		//修改course lesson number
		url := "http://127.0.0.1:8080/api/course/lessonnum/"+c.Query("course_id")+"/"+strconv.Itoa(countLesson);
		contentType := "application/json"
		data := `{"id":courseId,"lesson_num":1}`
		resp, err := http.Post(url, contentType, strings.NewReader(data))
		if err != nil {
			panic(err)
		}
		defer resp.Body.Close()
		b, err := ioutil.ReadAll(resp.Body)
		if err != nil {
			panic(err)
		}
		courseMessage :=string(b)
		//修改完毕
		c.JSON(http.StatusOK,gin.H{
			"message":courseMessage,
		})
	}
}

func _updateLessonCount(db *pg.DB,courseId int) string{
	return("")
}

//改
func PostLesson(db *pg.DB) gin.HandlerFunc{
	return func(c *gin.Context){
		var form Lesson

		if err := c.ShouldBindJSON(&form); err != nil {
			c.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
			return
		}

		_,err := db.Model(&form).Table("lessons").Column("title","subtitle","summary","teacher","tag","lang","speech_lang","status","content","start_date","duration").WherePK().Update()
		if err != nil {
			panic(err)
		}
		c.JSON(http.StatusOK,gin.H{
			"message":"update ok",
		})
	}
}


//删
func DeleteLesson(db *pg.DB) gin.HandlerFunc{
	return func(c *gin.Context){
		id,err := strconv.ParseInt(c.Param("lid"),10,64)
		if err != nil {
			panic(err)
		}
		lesson := &Lesson{
			Id:int(id),
		}
		//删之前获取 course_id
		_, err = db.Model(lesson).WherePK().Delete()
		if err != nil {
			panic(err)
		}

		
		c.JSON(http.StatusOK,gin.H{
			"message":"delete "+c.Param("lid"),
		})
	}
}