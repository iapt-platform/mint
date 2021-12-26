package mint

import (
	"encoding/json"
	"fmt"
	"net/http"
	"strconv"
	"time"

	"github.com/gin-gonic/gin"
	"github.com/go-pg/pg/v10"
	"github.com/go-redis/redis/v8"
)

type Course struct {
	Id          int    `form:"id" json:"id" binding:"required"`
	Uid         string `form:"uid" json:"uid"`
	ParentId    int    `form:"parent_id" json:"parent_id"`
	PrParentId  int    `form:"pr_parent_id" json:"pr_parent_id"`
	Cover       string
	Title       string    `form:"title" json:"title"`
	Subtitle    string    `form:"subtitle" json:"subtitle"`
	Summary     string    `form:"summary" json:"summary"`
	TeacherId   int       `form:"teacher" json:"teacher"`
	Lang        string    `form:"lang" json:"lang"`
	Speech_lang string    `form:"speech_lang" json:"speech_lang"`
	LessonNum   int       `form:"lesson_num" json:"lesson_num"`
	StartAt     time.Time `form:"start_at" json:"start_at"`
	EndAt       time.Time `form:"end_at" json:"end_at"`
	Content     string    `form:"content" json:"content"`
	ContentType string    `form:"content" json:"content_type"`
	Status      string    `form:"status" json:"status"`
	EditorId    int       `json:"editor_id"`
	StudioId    int       `json:"studio_id"`
	CreatorId   int       `json:"creator_id"`
	Version     int
	DeletedAt   time.Time
	CreatedAt   time.Time
	UpdatedAt   time.Time
}

func panicIf(err error) {
	if err != nil {
		panic(err)
	}
}

//查询display a list of all Courses
func CoursesIndex(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {
		//按照标题搜索，或者按照course列出子课程
		view := c.Query("view")
		studio := c.DefaultQuery("studio", "")
		status := c.DefaultQuery("status", "public")

		columns := "id , parent_id , title , subtitle , updated_at"
		var courses []Course
		var err error
		if studio == "" {
			switch view {
			case "title":
				title := c.Query("title")
				err = db.Model(&courses).ColumnExpr(columns).Where("title like ?", title+"%").Where("status = ?", "public").Select()
			case "course":
				err = db.Model(&courses).ColumnExpr(columns).Where("parent_id = 0").Where("status = ?", "public").Select()
			case "lessson":
				iCourseId := c.Query("courseid")
				err = db.Model(&courses).ColumnExpr(columns).Where("parent_id = ?", iCourseId).Where("status = ?", "public").Select()
			}
		} else {
			if status == "all" {
				// 列出 studio course 全部数据 需要有 member权限
				//TODO studio 鉴权
				switch view {
				case "title":
					title := c.Query("title")
					err = db.Model(&courses).ColumnExpr(columns).Where("title like ?", title+"%").Where("studio_id = ?", c.Query("studio")).Select()
				case "course":
					err = db.Model(&courses).ColumnExpr(columns).Where("parent_id = 0").Where("studio_id = ?", c.Query("studio")).Select()
				case "lessson":
					err = db.Model(&courses).ColumnExpr(columns).Where("parent_id = ?", c.Query("courseid")).Where("studio_id = ?", c.Query("studio")).Select()
				}
			} else {
				// 列出 studio course 全部公开数据
				switch view {
				case "title":
					title := c.Query("title")
					err = db.Model(&courses).ColumnExpr(columns).Where("title like ?", title+"%").Where("studio_id = ?", c.Query("studio")).Where("status = ?", "public").Select()
				case "course":
					err = db.Model(&courses).ColumnExpr(columns).Where("parent_id = 0").Where("studio_id = ?", c.Query("studio")).Where("status = ?", "public").Select()
				case "lessson":
					err = db.Model(&courses).ColumnExpr(columns).Where("parent_id = ?", c.Query("courseid")).Where("studio_id = ?", c.Query("studio")).Where("status = ?", "public").Select()
				}
			}

		}
		if err != nil {
			panic(err)
		}

		c.JSON(http.StatusOK, gin.H{
			"ok":   true,
			"data": courses,
		})
	}
}

//return an HTML form for creating a new Courses
func CoursesNew(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {
		//TODO 业务逻辑
		c.HTML(http.StatusOK, "courses/new.html", gin.H{
			"message": "ok",
		})
	}
}

//新建create a new Courses
func CoursesCreate(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {
		var form Course

		if err := c.ShouldBindJSON(&form); err != nil {
			c.JSON(http.StatusBadRequest, gin.H{
				"ok":    false,
				"error": err.Error(),
			})
			return
		}

		//TODO studio 鉴权

		if form.Title == "" {
			c.JSON(http.StatusBadRequest, gin.H{
				"ok":    false,
				"error": ":no-title",
			})
			return
		}

		if form.ParentId != 0 {
			//子课程
			//先查询母课程是否存在
			countCourse, err := db.Model((*Course)(nil)).Where("id = ?", form.ParentId).Count()
			if err != nil {
				panic(err)
			}
			if countCourse == 0 {
				c.JSON(http.StatusBadRequest, gin.H{
					"ok":    false,
					"error": ":no-parent-course",
				})
				return
			}
		}
		//TODO 获取 userid
		form.EditorId = 1
		form.CreatorId = 1

		_, err := db.Model(&form).Column("title", "status", "studio_id", "editor_id", "creator_id").Insert()
		panicIf(err)

		//子课程更新母课程LessonNum
		if form.ParentId != 0 {
			//获取课程数量
			countLesson, err := db.Model((*Course)(nil)).Where("parent_id = ?", form.ParentId).Count()
			if err != nil {
				panic(err)
			}

			parentCourse := Course{Id: form.ParentId, LessonNum: countLesson}

			_, err = db.Model(&parentCourse).Column("lesson_num").WherePK().Update()
			if err != nil {
				panic(err)
			}
		}

		c.JSON(http.StatusOK, gin.H{
			"ok":   true,
			"data": form,
		})
	}
}

//查询display a specific Courses
func CoursesShow(db *pg.DB, rdb *redis.Client) gin.HandlerFunc {
	return func(c *gin.Context) {
		id, err := strconv.Atoi(c.Param("id"))
		if err != nil {
			panic(err)
		}

		rkey := "course/:id"
		n, err := rdb.HExists(ctx, rkey, c.Param("id")).Result()
		if err == nil && n {
			val, err := rdb.HGet(ctx, rkey, c.Param("id")).Result()
			if err == nil {
				var redisData Course
				json.Unmarshal([]byte(val), &redisData)
				c.JSON(http.StatusOK, gin.H{
					"status": "success",
					"data":   redisData,
				})
				return
			} else {
				//有错误
				fmt.Println("redis error")
			}
		} else {
			fmt.Println("redis error or key not exist")
		}
		// Select by primary key.

		course := &Course{Id: id}
		err = db.Model(course).WherePK().First()

		if err != nil {
			panic(err)
		}

		c.JSON(http.StatusOK, gin.H{
			"ok":   true,
			"data": course,
		})
		//写入redis
		jsonData, err := json.Marshal(course)
		if err == nil {
			rdb.HSet(ctx, rkey, c.Param("id"), string(jsonData))
		}
	}
}

//return an HTML form for edit a Courses
func CoursesEdit(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {
		//TODO 业务逻辑
		c.HTML(http.StatusOK, "courses/edit.html", gin.H{
			"message": "ok",
		})
	}
}

//update a specific Courses

func CoursesUpdate(db *pg.DB, rdb *redis.Client) gin.HandlerFunc {
	return func(c *gin.Context) {
		var form Course

		if err := c.ShouldBindJSON(&form); err != nil {
			c.JSON(http.StatusBadRequest, gin.H{
				"ok":    false,
				"error": err.Error(),
			})
			return
		}

		_, err := db.Model(&form).Column("title", "subtitle", "summary", "teacher_id", "lang", "speech_lang", "status", "content", "content_type", "start_at", "end_at").WherePK().Update()
		if err != nil {
			panic(err)
		}
		c.JSON(http.StatusOK, gin.H{
			"ok":   true,
			"data": form,
		})
		//delete redis
		rkey := "course/:id"
		rdb.HDel(ctx, rkey, c.Param("id"))
	}
}

//删
//delete a specific Courses
func CoursesDestroy(db *pg.DB, rdb *redis.Client) gin.HandlerFunc {
	return func(c *gin.Context) {
		id, err := strconv.Atoi(c.Param("id"))
		if err != nil {
			panic(err)
		}
		course := &Course{
			Id: id,
		}

		_, err = db.Model(course).WherePK().Where("parent_id = ?", id).Delete()
		if err != nil {
			panic(err)
		}
		rkey := "course/:id"
		rdb.HDel(ctx, rkey, c.Param("id"))

		c.JSON(http.StatusOK, gin.H{
			"ok":   true,
			"data": course,
		})
	}
}
