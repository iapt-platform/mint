package mint

import (
	"net/http"
	"github.com/gin-gonic/gin"
	"github.com/go-pg/pg/v10"
	//"github.com/go-redis/redis/v8"
	"time"
)

type ActiveLogs struct {
	Id     int `form:"id" json:"id" `
	UserId int `form:"user_id" json:"user_id" `
	ActiveType string `form:"active_type" json:"active_type"`
	Content string `form:"content" json:"content"`
	Timezone string `form:"timezone" json:"timezone"`

    CreatedAt time.Time
    UpdatedAt time.Time
}

type ActiveTimeFrames struct {
	Id     int `form:"id" json:"id" `
	UserId int `form:"user_id" json:"user_id" `
	Hit int `form:"hit" json:"hit"`
	Timezone string `form:"timezone" json:"timezone"`

    CreatedAt time.Time
    UpdatedAt time.Time
}
type ActiveDayFrames struct {
	Id        int `form:"id" json:"id" `
	UserId    int `form:"user_id" json:"user_id" `
    Date      time.Time

	Duration  int `form:"duration" json:"duration"`
	hit       int `form:"hit" json:"hit"`

    CreatedAt time.Time
    UpdatedAt time.Time
}

//display a list of all groups
func ActiveIndex(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {
		timerange := c.Query("range")
		userid := c.Query("userid")

		switch timerange {
		case "day":
			var dayFrames []ActiveDayFrames
			err := db.Model(&dayFrames).Column("id","date","Duration","hit").Where("user_id = ?",userid).Select()
			if err != nil {
				panic(err)
			}
			c.JSON(http.StatusOK, gin.H{
				"data": dayFrames,
			})			
		case "frame":
			var timeFrames []ActiveTimeFrames
			err := db.Model(&timeFrames).Column("id","hit","timezone","created_at","updated_at").Where("user_id = ?",userid).Select()
			if err != nil {
				panic(err)
			}
			c.JSON(http.StatusOK, gin.H{
				"data": timeFrames,
			})
		case "event":
			start := c.Query("start")
			end := c.Query("end")

			var activeLogs []ActiveLogs
			err := db.Model(&activeLogs).Column("active_type","content","timezone","created_at").Where("user_id = ?",userid).Where("created_at > ?",start).Where("created_at < ?",end).Select()
			if err != nil {
				panic(err)
			}
			c.JSON(http.StatusOK, gin.H{
				"data": activeLogs,
			})
		}		

	}
}


//create a new group
func ActiveCreate(db *pg.DB) gin.HandlerFunc{
	return func(c *gin.Context){
	
		active_type := c.Query("active_type")
		content := c.Query("content")

		activeLogs := &ActiveLogs{
			ActiveType:   active_type,
			Content: content,
			UserId:1,//TODO user_id
		}
		_, err := db.Model(activeLogs).Insert()
		if err != nil {
			panic(err)
		}
		//TODO 补充业务逻辑
		//建立成功
		c.JSON(http.StatusOK,gin.H{
			"message":"ok",
		})
	}
}

