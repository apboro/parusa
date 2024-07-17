<script>
export default {
    name: "TripInfo",
    props:{
        trip: Object
    }
}
</script>

<template>
    <div class="ap-showcase__info">
        <p class="ap-showcase__info-title">
            Информация об экскурсии: <br>
            <span class="ap-showcase__info-name">{{ trip['excursion'] }}</span>
        </p>
        <div class="ap-showcase__info-bottom">
            <div class="ap-showcase__info-time">
                <img src="../assets/icon-time.png" alt="" class="ap-showcase__info-icon">
                <p>
                    Время в пути: <br>
                    <span class="ap-not-brake">{{ trip['duration'] }} мин.</span>
                </p>
            </div>
            <div class="ap-showcase__info-tickets">
                <img src="../assets/icon-ticket.png" alt="" class="ap-showcase__info-icon">
                <p>
                    Осталось билетов: <br>
                    <span class="ap-not-brake">{{ trip['tickets_left'] }} шт.</span>
                </p>
            </div>


        </div>
    </div>

</template>

<style scoped lang="scss">
    .ap-showcase__info {
        width: 373px;

        &-title {
            width: 100%;
            font-family: Gilroy;
            font-size: 20px;
            font-weight: 700;
            color: #241B5B;
            height: max-content;
        }

        &-name {
            font-weight: 500;
            margin-top: 30px;
            display: inline-block;
        }

        &-bottom {
            display: flex;
            flex-wrap: wrap;
        }

        &-icon {
            width: 32px;
            height: 32px;
            margin-bottom: -10px;
        }

        &-time {
            width: 49%;

            p {
                color: #241B5B;
                font-family: Gilroy;
                font-size: 18px;
                font-weight: 500;

                span {
                    color: #E83B4E;
                    font-size: 20px;
                    display: inline-block;
                    margin-top: 10px;
                }
            }
        }

        &-tickets {
            width: 49%;

            p {
                color: #241B5B;
                font-family: Gilroy;
                font-size: 18px;
                font-weight: 500;

                span {
                    color: #E83B4E;
                    font-size: 20px;
                    display: inline-block;
                    margin-top: 10px;
                }
            }
        }
    }
</style>
