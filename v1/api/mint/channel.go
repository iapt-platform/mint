package mint

import (
	"net/http"
	"strconv"
	"fmt"
	"github.com/gin-gonic/gin"
	"github.com/go-pg/pg/v10"
	"github.com/go-redis/redis/v8"
	"time"
)


type Channel struct {
	Id     int `form:"id" json:"id" `
	Uid string `form:"uid" json:"uid" `
	Title string `form:"title" json:"title"`
	Summary string `form:"summary" json:"summary"`
	Lang string `lang:"summary" json:"lang"`

	Setting string `form:"setting" json:"setting"`
	Status string `form:"status" json:"status"`

	OwnerId int
	Version int
    DeletedAt time.Time
    CreatedAt time.Time
    UpdatedAt time.Time
}


//display a list of all channels
func ChannelsIndex(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {
		name:= c.DefaultQuery("name","")

		// TODO 补充业务逻辑
		var channels []Channel
		err := db.Model(&channels).Column("id","name").Where("name like ?",name+"%").Select()
		if err != nil {
			panic(err)
		}
		
		c.JSON(http.StatusOK, gin.H{
			"data": channels,
		})
	}
}

//return an HTML form for creating a new channel
func ChannelsNew(db *pg.DB) gin.HandlerFunc{
	return func(c *gin.Context){
		//TODO 业务逻辑
		c.HTML(http.StatusOK,"channels/new.html",gin.H{
			"message":"ok",
		})
	}
}
//create a new channel
func ChannelsCreate(db *pg.DB) gin.HandlerFunc{
	return func(c *gin.Context){
	
		name := c.Query("title")
		status := c.DefaultQuery("status","private")

		newChannel := &Channel{
			Title:   name,
			Status: status,
			OwnerId:1,//TODO user_id
		}
		_, err := db.Model(newChannel).Insert()
		if err != nil {
			panic(err)
		}

		//建立成功
		c.JSON(http.StatusOK,gin.H{
			"message":"ok",
		})
	}
}

//display a specific Channel
func ChannelsShow(db *pg.DB, rdb *redis.Client) gin.HandlerFunc {
	return func(c *gin.Context) {
		id,err := strconv.Atoi(c.Param("id"))
		if err != nil {
			panic(err)
		}
		fmt.Println("get channel id=" + c.Param("id"))
		rkey := "channel://id/"+c.Param("id")
		n, err := rdb.Exists(ctx,rkey).Result()
		if err != nil  {
			fmt.Println(err)
		}else if n == 0 {
			fmt.Println("redis key not exist")
		}else{
			fmt.Println("redis key exist")
			val, err := rdb.HGetAll(ctx, rkey).Result()
			if err != nil || val == nil {
				//有错误或者没查到
				fmt.Println("redis error")
					
			}else{
				fmt.Println("redis no error")
				c.JSON(http.StatusOK, gin.H{
					"data": val,
				})
				return
			}	
		}

		channel := &Channel{Id: id}
		err = db.Model(channel).Column("id","uid","name","description","description_type","owner_id","setting","status","version","updated_at").WherePK().Select()
		if err != nil {
			panic(err)
		}			
		c.JSON(http.StatusOK, gin.H{
			"data": channel,
		})
		//写入redis
		rdb.HSet(ctx,rkey,"id",channel.Id)
		rdb.HSet(ctx,rkey,"uid",channel.Uid)
		rdb.HSet(ctx,rkey,"title",channel.Title)
		rdb.HSet(ctx,rkey,"summary",channel.Summary)
		rdb.HSet(ctx,rkey,"lang",channel.Lang)
		rdb.HSet(ctx,rkey,"owner_id",channel.OwnerId)
		rdb.HSet(ctx,rkey,"setting",channel.Setting)
		rdb.HSet(ctx,rkey,"status",channel.Status)
		rdb.HSet(ctx,rkey,"version",channel.Version)
		rdb.HSet(ctx,rkey,"updated_at",channel.UpdatedAt)
		rdb.HSet(ctx,rkey,"created_at",channel.CreatedAt)
			
	}
}

//return an HTML form for edit a channel
func ChannelsEdit(db *pg.DB) gin.HandlerFunc{
	return func(c *gin.Context){
		//TODO 业务逻辑
		c.HTML(http.StatusOK,"channels/edit.html",gin.H{
			"name":"ok",
		})
	}
}

//update a specific channel
func ChannelsUpdate(db *pg.DB,rdb *redis.Client) gin.HandlerFunc{
	return func(c *gin.Context){
		var form Channel

		if err := c.ShouldBindJSON(&form); err != nil {
			c.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
			return
		}

		//补充业务逻辑
		_,err := db.Model(&form).Column("name","description","description_type","status","setting").WherePK().Update()
		if err != nil {
			panic(err)
		}
		c.JSON(http.StatusOK,gin.H{
			"message":form,
		})
		//delete redis
		rkey := "channel://id/"+strconv.Itoa(form.Id)
		rdb.Del(ctx,rkey)
	}
}


//delete a specific channel
func ChannelsDestroy(db *pg.DB ,rdb *redis.Client) gin.HandlerFunc{
	return func(c *gin.Context){
		id,err := strconv.Atoi(c.Param("id"))
		if err != nil {
			panic(err)
		}
		channel := &Channel{
			Id:int(id),
		}

		_, err = db.Model(channel).WherePK().Delete()
		if err != nil {
			panic(err)
		}

		rkey := "channel://id/"+c.Param("id")
		rdb.Del(ctx,rkey)
		
		c.JSON(http.StatusOK,gin.H{
			"message": c.Param("id"),
		})



	}
}