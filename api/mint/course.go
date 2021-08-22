package mint

import (
	"fmt"
	"github.com/gin-gonic/gin"
	"github.com/go-pg/pg/v10"
	"net/http"
	"strconv"
	"time"
)

type Course struct {
	Id          int `form:"id" json:"id" binding:"required"`
	Cover       string
	Title       string `form:"title" json:"title"`
	Subtitle    string `form:"subtitle" json:"subtitle"`
	Summary     string `form:"summary" json:"summary"`
	Teacher     int    `form:"teacher" json:"teacher"`
	Lang        string `form:"lang" json:"lang"`
	Speech_lang string `form:"speech_lang" json:"speech_lang"`
	Status      int    `form:"status" json:"status"`
	Content     string `form:"content" json:"content"`
	Creator     int
	LessonNum   int `form:"lesson_num" json:"lesson_num"`
	Version     int
	CreatedAt   time.Time
	UpdatedAt   time.Time
}

//查询
func GetCourse(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {
		cid, err := strconv.Atoi(c.Param("cid"))
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
			"message": "course-" + course.Title,
		})
	}
}

//查询
func GetCourseByTitle(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {
		ctitle := c.Param("ctitle")

		// TODO 在这里进行db操作
		// Select user by primary key.
		var courses []Course
		err := db.Model(&courses).Column("id", "title", "subtitle").Where("title like ?", ctitle+"%").Select()
		if err != nil {
			panic(err)
		}

		c.JSON(http.StatusOK, gin.H{
			"message": courses,
		})
	}
}

//新建
func PutCourse(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {
		title := c.Query("title")
		status1, err := strconv.ParseInt(c.Query("status"), 10, 64)
		if err != nil {
			panic(err)
		}
		fmt.Println("title:" + title)

		newCouse := &Course{
			Title:   title,
			Status:  int(status1),
			Teacher: 1,
			Creator: 1,
		}
		_, err = db.Model(newCouse).Insert()
		if err != nil {
			panic(err)
		}
		c.JSON(http.StatusOK, gin.H{
			"message": "new-Ok- hello " + title,
		})
	}
}

//改
func PostCourse(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {
		var form Course

		if err := c.ShouldBindJSON(&form); err != nil {
			c.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
			return
		}

		_, err := db.Model(&form).Table("courses").Column("title", "subtitle", "summary", "teacher", "lang", "speech_lang", "status", "content").WherePK().Update()
		if err != nil {
			panic(err)
		}
		c.JSON(http.StatusOK, gin.H{
			"message": "update ok",
		})
	}
}

//补
func PatchLessonNumInCousrse(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {
		var form Course

		if err := c.ShouldBindJSON(&form); err != nil {
			c.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
			return
		}

		_, err := db.Model(&form).Column("lesson_num").WherePK().Update()
		if err != nil {
			panic(err)
		}
		c.JSON(http.StatusOK, gin.H{
			"message": "patch ok",
		})
	}
}

//删
func DeleteCourse(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {
		id, err := strconv.Atoi(c.Param("cid"))
		if err != nil {
			panic(err)
		}
		course := &Course{
			Id: id,
		}
		_, err = db.Model(course).WherePK().Delete()
		if err != nil {
			panic(err)
		}
		c.JSON(http.StatusOK, gin.H{
			"message": "delete " + c.Param("cid"),
		})
	}
}
