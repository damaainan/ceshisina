public class WhiteHumanFactory extends AbstractHumanFactory {

	public Human createHuman() {
		return new YellowHuman();
	}
}