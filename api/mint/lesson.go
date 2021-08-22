package mint

import (
	"io/ioutil"
	"net/http"
	/*"strings"*/
	"bytes"
	"encoding/json"
	"fmt"
	"github.com/gin-gonic/gin"
	"github.com/go-pg/pg/v10"
	"strconv"
	"time"
)

type Lesson struct {
	Id          int `form:"id" json:"id" binding:"required"`
	CourseId    int `form:"course_id" json:"course_id" binding:"required"`
	Cover       string
	Title       string `form:"title" json:"title" binding:"required"`
	Subtitle    string `form:"subtitle" json:"subtitle" binding:"required"`
	Summary     string `form:"summary" json:"summary" binding:"required"`
	Teacher     int    `form:"teacher" json:"teacher" binding:"required"`
	Lang        string `form:"lang" json:"lang" binding:"required"`
	Speech_lang string `form:"speech_lang" json:"speech_lang" binding:"required"`
	Status      int    `form:"status" json:"status" binding:"required"`
	Content     string `form:"content" json:"content" binding:"required"`
	Creator     int
	StartDate   time.Time `form:"date" json:"date" binding:"required"`
	Duration    int64     `form:"duration" json:"duration" binding:"required"`
	Version     int64
	CreatedAt   time.Time
	UpdatedAt   time.Time
}

//查询
func GetLesson(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {
		lid, err := strconv.ParseInt(c.Param("lid"), 10, 64)
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
			"message": "lesson-" + lesson.Title,
		})
	}
}

//查询
func GetLessonByTitle(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {
		title := c.Param("ltitle")

		// TODO 在这里进行db操作
		// Select user by primary key.
		var lessons []Lesson
		err := db.Model(&lessons).Column("id", "title", "subtitle").Where("title like ?", title+"%").Select()
		if err != nil {
			panic(err)
		}

		c.JSON(http.StatusOK, gin.H{
			"message": lessons,
		})
	}
}

/*新建
新建课以后，查询这个course 里有几个lesson 然后更新 courese 的 lesson_num
*/
//PUT http://127.0.0.1:8080/api/lesson?title=lesson-one&course_id=1&status=10
func PutLesson(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {

		title := c.Query("title")
		courseId, err := strconv.Atoi(c.Query("course_id"))
		status1, err := strconv.Atoi(c.Query("status"))
		if err != nil {
			panic(err)
		}

		newLesson := &Lesson{
			Title:    title,
			CourseId: courseId,
			Status:   status1,
			Teacher:  0,
			Creator:  1,
		}
		_, err = db.Model(newLesson).Insert()
		if err != nil {
			panic(err)
		}
		//修改 course 的 lesson_num
		courseMessage := _updateLessonCount(db, courseId)

		c.JSON(http.StatusOK, gin.H{
			"message": courseMessage,
		})
	}
}

func _updateLessonCount(db *pg.DB, courseId int) string {
	//查询这个course 里面有几个课程
	countLesson, err := db.Model((*Lesson)(nil)).Where("course_id = ?", courseId).Count()
	if err != nil {
		panic(err)
	}

	//修改course lesson number
	url := "http://127.0.0.1:8080/api/course/lessonnum"
	values := Course{
		Id:        courseId,
		LessonNum: countLesson,
	}
	json_data, err := json.Marshal(values)
	if err != nil {
		panic(err)
	}

	client := &http.Client{}
	req, err := http.NewRequest("PATCH", url, bytes.NewBuffer(json_data))
	if err != nil {
		panic(err)
	}
	req.Header.Set("Content-Type", "application/json")
	resp, err := client.Do(req)
	if err != nil {
		panic(err)
	}
	defer resp.Body.Close()
	b, err := ioutil.ReadAll(resp.Body)
	if err != nil {
		panic(err)
	}

	return (string(b))
}

//改
func PostLesson(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {
		var form Lesson

		if err := c.ShouldBindJSON(&form); err != nil {
			c.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
			return
		}

		_, err := db.Model(&form).Table("lessons").Column("title", "subtitle", "summary", "teacher", "tag", "lang", "speech_lang", "status", "content", "start_date", "duration").WherePK().Update()
		if err != nil {
			panic(err)
		}
		c.JSON(http.StatusOK, gin.H{
			"message": "update ok",
		})
	}
}

//删
func DeleteLesson(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {
		id, err := strconv.ParseInt(c.Param("lid"), 10, 64)
		if err != nil {
			panic(err)
		}
		lesson := &Lesson{
			Id:       int(id),
			CourseId: int(0),
		}
		//删之前获取 course_id
		err = db.Model(lesson).Column("course_id").WherePK().Select()
		if err != nil {
			panic(err)
		}
		course_id := lesson.CourseId

		_, err = db.Model(lesson).WherePK().Delete()
		if err != nil {
			panic(err)
		}

		//修改 course 的 lesson_num
		courseMessage := _updateLessonCount(db, course_id)

		c.JSON(http.StatusOK, gin.H{
			"message":    "delete " + c.Param("lid"),
			"lesson_num": courseMessage,
		})
	}
}
