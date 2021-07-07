package mint

import (
	"net/http"

	"github.com/gin-gonic/gin"
	"github.com/go-pg/pg/v10"
)

type Login struct {
	User     string `form:"user" json:"user" binding:"required"`
	Password string `form:"password" json:"password" binding:"required"`
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

func GetDemo(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {
		// TODO 在这里进行db操作
		_, err := db.Exec("SELECT CURRENT_TIMESTAMP")
		if err != nil {
			panic(err)
		}
		c.JSON(http.StatusOK, gin.H{
			"message": "pong",
		})
	}
}
