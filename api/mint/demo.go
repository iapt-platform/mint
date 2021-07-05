package mint

import (
	"github.com/gin-gonic/gin"
	"gorm.io/gorm"
)

func GetDemo(_db *gorm.DB) gin.HandlerFunc {
	return func(c *gin.Context) {
		// TODO 在这里进行db操作
		c.JSON(200, gin.H{
			"message": "pong",
		})
	}
}
